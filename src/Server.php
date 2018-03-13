<?php

namespace sforsman\Rest;

use \Exception;
use \stdClass;
use League\Route\RouteCollection as Router;
use League\Route\Strategy\RestfulStrategy;
use League\Container\ContainerInterface;
use League\Container\Container;
use League\Event\EmitterInterface;
use League\Event\Emitter;
use League\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception as HttpException;

class Server
{
  protected $container;
  protected $router;
  protected $emitter;
  protected $services;
  protected $previousErrorHandler;

  public function __construct(EmitterInterface $emitter = null, ContainerInterface $container = null)
  {
    if($container === null) {
      $container = new Container();
    }
    if($emitter === null) {
      $emitter = new Emitter();
      // Since we are creating the default emitter, make sure the same instance is in the container
      $container->add(EmitterInterface::class, $emitter);
    }

    $this->container   = $container;
    $this->router      = new Router($this->container);
    // TODO: Don't force RestfulStrategy, move this to AbstractJsonService
    $this->router->setStrategy(new RestfulStrategy());
    $this->emitter     = $emitter;
    $this->services = [];
  }

  public function register($entity, $class, $version = 'v1')
  {
    if(!isset($this->services[$version])) {
      $this->services[$version] = [];
    } elseif(isset($this->services[$version][$entity])) {
      throw new Exception('A service for the entity "' . $entity . '" has already been registered (' . $version . ')');
    } 
    if(!preg_match('|^[0-9A-Za-z_-]+$|', $entity)) {
      throw new Exception('The entity name "' . $entity . '" is invalid');
    }
    if(!preg_match('|^[0-9A-Za-z_-]+$|', $version)) {
      throw new Exception('The version name "' . $version . '" is invalid');
    }

    $this->services[$version][$entity] = $class;
    
    $path = '/' . $version . '/' . $entity;

    $methods = ['GET','POST','PUT','PATCH','DELETE','OPTIONS'];

    foreach($methods as $request_method) {
      $closure = function(Request $request, array $args = []) use ($request_method, $class, $entity, $version) {
        $service = new stdClass(); // This only exists to identify the situation where container throws an exception
        try {
          $service = $this->container->get($class);
          if($service instanceof ServiceInterface) {
            $eventArgs = [
              'request' => $request, 
              'args'    => $args, 
              'service' => $service, 
              'entity'  => $entity,
              'version' => $version,
              'method'  => $request_method,
            ];
            $this->emitter->emit(Event::named('invoke'), $eventArgs);
            return $service->invoke($request_method, $args, $request);
          } else {
            throw new Exception('The service "' . $class . '" does not implement ServerInterface');
          }
        } catch(RestException $e) {
          // These are 'soft' exceptions, for which we want to show the user of the API
          // the actual message of the exception. Class will be determined based on the
          // HTTP code

          // By listening for these events, the API can implement logging, for an example
          $eventArgs = [
            'exception' => $e, 
            'request'   => $request, 
            'args'      => $args, 
            'service'   => $service,
            'entity'    => $entity,
            'version'   => $version,
            'method'    => $request_method,
          ];
          $this->emitter->emit(Event::named('exception'), $eventArgs);

          switch($e->getCode())
          {
            case 400: throw new BadRequestException($e->getMessage());
            case 403: throw new ForbiddenException($e->getMessage());
            case 404: throw new NotFoundException($e->getMessage());
            case 405: throw new HttpException(405, $e->getMessage());
            default:  throw new HttpException(500, $e->getMessage());
          }
        } catch(Exception $e) {
          // By listening for these events, the API can implement logging, for an example
          $eventArgs = [
            'exception' => $e, 
            'request'   => $request, 
            'args'      => $args, 
            'service'   => $service, 
            'entity'    => $entity,
            'version'   => $version,
            'method'    => $request_method,
          ];
          $this->emitter->emit(Event::named('exception'), $eventArgs);

          // For other Exceptions we just show a server error
          throw new HttpException(500, 'Internal server error');
        }
      };

      if($request_method === 'POST') {
        $this->router->addRoute($request_method, $path . '/{subentity}', $closure);
        $this->router->addRoute($request_method, $path, $closure);
      } elseif($request_method === 'GET' or $request_method === 'OPTIONS') {
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
        $this->router->addRoute($request_method, $path . '/{subentity}/{id}', $closure);
        $this->router->addRoute($request_method, $path, $closure);
      } else {
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
        $this->router->addRoute($request_method, $path . '/{subentity}/{id}', $closure);
      }
    }
  }

  public function run(Request $request = null)
  {
    if($request === null) {
      $request  = $this->container->get(Request::class);
    } else {
      // We need to replace the Request instance in the DIC
      $this->container->add(Request::class, $request);
    }
    $dispatcher = $this->router->getDispatcher();
    $method     = $request->getMethod();
    $path       = $request->getPathInfo();

    $response = $dispatcher->dispatch($method, $path);

    $eventArgs = [
      'response' => $response, 
    ];
    $this->emitter->emit(Event::named('response_ready'), $eventArgs);

    return $response;
  }

  public function registerErrorHandlers()
  {
    $this->previousErrorHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) {
      $this->respondError("PHP error {$errno}: {$errstr} @ {$errfile}:{$errline}", func_get_args());
     }, E_ALL);

    // Ensure fatal errors get logged and a clean error is shown to the user
    register_shutdown_function(function() {
      $error = error_get_last();
      if($error !== null) {
        $errorArgs = array_values($error);
        list($errno,$errstr,$errfile,$errline) = $errorArgs;
        $this->respondError("Fatal error {$errno}: {$errstr} @ {$errfile}:{$errline}", $errorArgs);
      }
    });

    // These only prevent the PHP default stuff flying into the browser (or to Apache's/PHP's error logs)
    ini_set('display_errors', '0');
    ini_set('log_errors', '0');
    error_reporting(0);
  }

  protected function respondError($errorStr, $errorArgs)
  {
    $this->emitter->emit(Event::named('error'), $errorStr, $errorArgs);

    http_response_code(500);
    Header('Content-type: application/json');
    echo json_encode([
      'status_code'=>500,
      'message'=>'Internal server error',
    ]);
    exit();
  }

  public function registerServiceLoader(ServiceLoaderInterface $loader)
  {
    foreach($loader->getServices() as $version=>$classes) {
      foreach($classes as $service=>$class) {
        $this->register($service, $class, $version);
      }
    }
  }
  
  public function getPreviousErrorHandler()
  {
    return $this->previousErrorHandler;
  }
}
