<?php

namespace sforsman\Rest;

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use League\Event\CallbackListener;
use League\Event\Emitter;

class BasicEventTest extends \PHPUnit_Framework_TestCase
{
  public function testInvoke()
  {
    $emitter = new Emitter();
    $callback = function($event, $parms) {
      $this->assertInstanceOf(TestiApi\v1\TestService::class, $parms['service']);
    };
    $emitter->addListener('invoke', CallbackListener::fromCallable($callback));

    $api = new Server($emitter);
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
  }

  public function testResponseReady()
  {
    $emitter = new Emitter();
    $callback = function($event, $parms) {
      $this->assertInstanceOf(Response::class, $parms['response']);
    };
    $emitter->addListener('response_ready', CallbackListener::fromCallable($callback));

    $api = new Server($emitter);
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
  }
}
