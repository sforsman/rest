<?php

namespace sforsman\Rest;

require_once __DIR__ . '/../vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
  public function testException()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/error/1', 'GET');
    $response = $api->run($request);
    $this->assertEquals($response->getStatusCode(), 500);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":500,"message":"Internal server error"}', $response->getContent());
  }

  public function testRestException()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/error/abcd', 'GET');
    $response = $api->run($request);
    $this->assertEquals($response->getStatusCode(), 400);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":400,"message":"Bad id"}', $response->getContent());
  }

  public function testRestExceptionNotFound()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/error/4', 'GET');
    $response = $api->run($request);
    $this->assertEquals($response->getStatusCode(), 404);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":404,"message":"Not found"}', $response->getContent());
  }
}
