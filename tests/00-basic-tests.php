<?php

namespace sforsman\Rest;

require __DIR__ . '/../vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BasicTest extends \PHPUnit_Framework_TestCase
{
  public function testEmptyServer()
  {
    $request = new Request();
    $api = new Server();

    $response = $api->run($request);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":404,"message":"Not Found"}', $response->getContent());
  }

  public function testInterface()
  {
    $api = new Server();
    $api->register('test', InterfaceService::class, 'v1');

    $request = Request::create('/v1/test', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'GET', 'args'=>[]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'GET', 'args'=>['id'=>'123']]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test', 'POST');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'POST', 'args'=>[]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'PUT');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'PUT', 'args'=>['id'=>'123']]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'DELETE', 'args'=>['id'=>'123']]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'PATCH');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $expected = json_encode(['method'=>'PATCH', 'args'=>['id'=>'123']]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
  }

  public function testNotAllowed()
  {
    $api = new Server();
    $api->register('test', InterfaceService::class, 'v1');

    $request = Request::create('/v1/test/123', 'POST');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Method Not Allowed"}', $response->getContent());

    $request = Request::create('/v1/test', 'PUT');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Method Not Allowed"}', $response->getContent());

    $request = Request::create('/v1/test', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Method Not Allowed"}', $response->getContent());

    $request = Request::create('/v1/test', 'PATCH');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Method Not Allowed"}', $response->getContent());
  }

  public function testEmptyJsonServer()
  {
    $api = new Server();
    $api->register('test', EmptyJsonServer::class, 'v1');

    $request = Request::create('/v1/test', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test', 'POST');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":400,"message":"Input data contained invalid JSON"}', $response->getContent());

    $request = Request::create('/v1/test', 'POST', [], [], [], [], json_encode(['test'=>'123']));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'PUT');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":400,"message":"Input data contained invalid JSON"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'PUT', [], [], [], [], json_encode(['test'=>'123']));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'DELETE');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'PATCH');
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":400,"message":"Input data contained invalid JSON"}', $response->getContent());

    $request = Request::create('/v1/test/123', 'PATCH', [], [], [], [], json_encode(['test'=>'123']));
    $response = $api->run($request);
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertJsonStringEqualsJsonString('{"status_code":405,"message":"Not implemented"}', $response->getContent());
  }

  public function testSimpleJsonServer()
  {
    $api = new Server();
    $api->register('test', SimpleJsonServer::class, 'v1');

    $request = Request::create('/v1/test', 'GET');
    $response = $api->run($request);
    $expected = json_encode(['ids'=>[1,2,3]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test', 'POST', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>1000, 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/123', 'PUT', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'DELETE');
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/123', 'PATCH', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
  }

  public function testDirectoryServiceLoader()
  {
    $api = new Server();
    $api->registerServiceLoader(new DirectoryServiceLoader(__DIR__ . '/services', '\\TestApi'));

       $request = Request::create('/v1/test', 'GET');
    $response = $api->run($request);
    $expected = json_encode(['ids'=>[1,2,3]]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'GET');
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test', 'POST', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>1000, 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/123', 'PUT', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $request = Request::create('/v1/test/123', 'DELETE');
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123']);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

    $data = ['test'=>'123'];
    $request = Request::create('/v1/test/123', 'PATCH', [], [], [], [], json_encode($data));
    $response = $api->run($request);
    $expected = json_encode(['id'=>'123', 'data'=> $data]);
    $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
  }
}

class InterfaceService implements ServiceInterface
{
  public function invoke($request_method, array $args, Request $request)
  {
    return ['method'=>$request_method, 'args'=>$args];
  }  
}

class EmptyJsonServer extends AbstractJsonService
{
}

class SimpleJsonServer extends AbstractJsonService
{
  public function get($id)
  {
    return ['id'=>$id];
  }

  public function all()
  {
    return ['ids'=>[1,2,3]];
  }

  public function post($data)
  {
    return ['id'=>1000, 'data'=>$data];
  }

  public function put($id, $data)
  {
    return ['id'=>$id, 'data'=>$data];
  }

  public function delete($id)
  {
    return ['id'=>$id];
  }

  public function patch($id, $data)
  {
    return ['id'=>$id, 'data'=>$data];
  }
}