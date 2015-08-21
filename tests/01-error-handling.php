<?php

namespace sforsman\Rest;

require __DIR__ . '/../vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
  public function testException()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/error/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":500,"message":"Internal server error"}', $response->getContent());
  }
}
