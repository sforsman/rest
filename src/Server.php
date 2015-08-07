<?php

namespace sforsman\Rest;

use League\Route\RouteCollection as Router;
use League\Route\Strategy\RestfulStrategy;
use League\Container\Container;
use Psr\Log\LoggerInterface;
use League\Event\Emitter;
use Symfony\Component\HttpFoundation\Request;

class Server
{
  protected $container;
  protected $router;
  protected $emitter;
  protected $controllers;

  public function __construct()
  {
    $this->container   = new Container();
    $this->router      = new Router($this->container);
    $this->emitter     = new Emitter();
    $this->controllers = [];
  }

  public function register($entity, $class, $version = 'v1')
  {
    $this->emitter->emit(Event::named('register.begin'), ['entity'=>$entity, 'class'=>$class, 'version'=>$version]);

    if(!isset($this->controllers[$version])) {
      $this->controllers[$version] = [];
    } elseif(isset($this->controllers[$version][$entity])) {
      throw new Exception('A controller for the entity "' . $entity . ' has already been registered (' . $version . ')"');
    } 
    if(preg_match('|^[0-9A-Za-z_-]+$|', $entity)) {
      throw new Exception('The entity name ' . $entity . ' is invalid');
    }
    if(preg_match('|^[0-9A-Za-z_-]+$|', $version)) {
      throw new Exception('The version name ' . $version . ' is invalid');
    }

    $this->controllers[$version][$entity] = $class;
    
    $path = '/' . $version . '/' . $entity;

    foreach(['GET','POST','PUT','PATCH','DELETE'] as $request_method) {
      $closure = function(Request $request, array $args = []) use ($request_method) {
        $controller = new $class();
        return $controller->invoke($request_method, $args);
      };

      if($request_method === 'POST') {
        $this->router->addRoute($request_method, $path, $closure);
      } elseif($request_method === 'GET') {
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
        $this->router->addRoute($request_method, $path, $closure);
      } else
        $this->router->addRoute($request_method, $path . '/{id}', $closure);
      }

      $this->emitter->emit(Event::named('register.method'), ['entity'=>$entity, 'class'=>$class, 'version'=>$version, 'path'=>$path, 'request_method'=>$request_method, 'method'=>$method]);
    }

    $this->emitter->emit(Event::named('register.end'), ['entity'=>$entity, 'class'=>$class, 'version'=>$version, 'path'=>$path]);
  }

  public function run(Request $request = null)
  {
    if($request === null) {
      $request = Request::createFromGlobals();
    }
    $this->request = $request;

    $this->emitter->emit(Event::named('run.begin'), ['request'=>$request]);

    $router->setStrategy(new RestfulStrategy());

    $dispatcher = $router->getDispatcher();
    $method     = $request->getMethod();
    $path       = $request->getPathInfo();

    $response = new ArrayObject();

    $this->emitter->emit(Event::named('dispatch.begin'), ['request'=>$request, 'method'=>$method, 'path'=>$path]);

    $response = $dispatcher->dispatch($method, $path);

    $this->emitter->emit(Event::named('dispatch.end'), ['request'=>$request, 'method'=>$method, 'path'=>$path, 'response'=>$response]);

    $response->send();
    
    $this->emitter->emit(Event::named('run.end'), $request, $response);
  }
}