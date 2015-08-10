<?php

namespace sforsman\Rest;

use \Exception;

abstract class AbstractJsonService implements ServiceInterface
{
  public function invoke($request_method, array $args)
  {
    if($request_method === 'GET' and empty($args['id'])) {
      $method = 'all'; 
    } else {
      $method = strtolower($request_method);
    }

    $invokeArgs = [];

    switch($method) {
      case 'get':
        $invokeArgs[] = $args['id'];
        break;
      case 'post':
        $invokeArgs[] = $this->parseInput(file_get_contents('php://input'));
        break;
      case 'put':
        $invokeArgs[] = $args['id'];
        $invokeArgs[] = $this->parseInput(file_get_contents('php://input'));
        break;
      case 'patch':
        $invokeArgs[] = $args['id'];
        $invokeArgs[] = $this->parseInput(file_get_contents('php://input'));
        break;
      case 'delete':
        $invokeArgs[] = $args['id'];
        break;
    }
    $response = call_user_method_array($method, $this, $invokeArgs);
    if(!is_array($response)) {
      throw new Exception('Invalid response data');
    }
    return $response;
  }

  public function get($id)
  {
    throw new RestException('Not implemented', 405);
  }

  public function all()
  {
    throw new RestException('Not implemented', 405);
  }

  public function post($data)
  {
    throw new RestException('Not implemented', 405);
  }

  public function put($id, $data)
  {
    throw new RestException('Not implemented', 405);
  }

  public function patch($id, $data)
  {
    throw new RestException('Not implemented', 405);
  }

  public function delete($id)
  {
    throw new RestException('Not implemented', 405);
  }

  public function parseInput($data)
  {
    return json_decode($data);
  }
}