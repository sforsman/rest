<?php

namespace sforsman\Rest;

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
  public function testBasic()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/test', 'OPTIONS');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString(json_encode([]), $response->getContent());

    $request = Request::create('/v1/test/123', 'OPTIONS');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString(json_encode([]), $response->getContent());

    $request = Request::create('/v1/test?parameter=123', 'OPTIONS');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString(json_encode([]), $response->getContent());
  }

  public function testNotImplemented()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/error', 'OPTIONS');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());
  }
}
