<?php

namespace sforsman\Rest;

require_once __DIR__ . '/../vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use League\Event\CallbackListener;
use League\Event\Emitter;

class SubentityTest extends \PHPUnit_Framework_TestCase
{
  public function testRegularRequests()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/subentity', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['ids'=>[1,2,3]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/subentity/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity', 'POST', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>1000, 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity/123', 'PUT', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/subentity/123', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity/123', 'PATCH', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
  }
  
  public function testSubentityRequests()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));


    $request = Request::create('/v1/subentity', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['ids'=>[1,2,3]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/subentity/test/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity/test', 'POST', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>1000, 'data'=> $data, 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity/test/123', 'PUT', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'data'=> $data, 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/subentity/test/123', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/subentity/test/123', 'PATCH', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'data'=> $data, 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
  }
  
  
  public function testUnallowedSubentityRequests()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

    $request = Request::create('/v1/test/subentity/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/subentity', 'POST', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/subentity/123', 'PUT', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test/subentity/123', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/subentity/123', 'PATCH', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['id'=>'123', 'data'=> $data, 'subentity'=>'test']);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());
  }
}