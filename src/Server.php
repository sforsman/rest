<?php

namespace sforsman\Rest;

use \Exception;
use League\Route\RouteCollection as Router;
use League\Route\Strategy\RestfulStrategy;
use League\Container\Container;
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

  public function __construct(Emitter $emitter = null)
  {
    if($emitter === null) {
      $emitter = new Emitter();
    }

    $this->container   = new Container();
    $this->router      = new Router($this->container);
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
    $emitter = $this->emitter;

    foreach(['GET','POST','PUT','PATCH','DELETE'] as $request_method) {
      $closure = function(Request $request, array $args = []) use ($request_method, $class, $emitter) {
        try {
          $service = new $class();
          if($service instanceof ServiceInterface) {
            return $service->invoke($request_method, $args);
          } else {
            throw new Exception('The service "' . $class . '" does not implement ServerInterface');
          }
        } catch(RestException $e) {
          // These are 'soft' exceptions, for which we want to show the user of the API
          // the actual message of the exception. Class will be determined based on the
          // HTTP code

          // By listening for these events, the API can implement logging, for an example
          $emitter->emit(Event::named('exception'), ['exception'=>$e, 'request'=>$request, 'args'=>$args]);

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
          $emitter->emit(Event::named('exception'), ['exception'=>$e, 'request'=>$request, 'args'=>$args]);

          // For other Exceptions we just show a server error
          throw new HttpException(500, 'Internal server error');
        }
      };

      if($request_method === 'POST') {
        $this->router->addRoute($request_method, $path, $closure);
      } elseif($request_method === 'GET') {
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
        $this->router->addRoute($request_method, $path, $closure);
      } else {
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
      }
    }
  }

  public function run(Request $request = null)
  {
    if($request === null) {
      $request = Request::createFromGlobals();
    }
    $this->request = $request;

    $dispatcher = $this->router->getDispatcher();
    $method     = $request->getMethod();
    $path       = $request->getPathInfo();

    $this->emitter->emit(Event::named('dispatch'), ['request'=>$request, 'method'=>$method, 'path'=>$path]);

    $response = $dispatcher->dispatch($method, $path);
    $response->send();
  }
}