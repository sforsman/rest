<?php

namespace sforsman\Rest;

use \Exception;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractJsonService implements ServiceInterface
{
  public function invoke($request_method, array $args, Request $request)
  {
    if($request_method === 'GET' and empty($args['id'])) {
      $method = 'all'; 
    } else {
      $method = strtolower($request_method);
    }

    $invokeArgs = [];

    switch($method) {
      case 'all':
        $invokeArgs[] = $request->query->all();
        break;
      case 'get':
        $invokeArgs[] = $args['id'];
        break;
      case 'post':
        $invokeArgs[] = $this->parseInput($request->getContent());
        break;
      case 'put':
        $invokeArgs[] = $args['id'];
        $invokeArgs[] = $this->parseInput($request->getContent());
        break;
      case 'patch':
        $invokeArgs[] = $args['id'];
        $invokeArgs[] = $this->parseInput($request->getContent());
        break;
      case 'delete':
        $invokeArgs[] = $args['id'];
        break;
    }

    if(isset($args['subentity'])) {
      $invokeArgs[] = $args['subentity'];
      $refmethod = new \ReflectionMethod($this, $method);
      if($refmethod->getNumberOfParameters() !== count($invokeArgs)) {
        throw new RestException('Not implemented', 405);
      }
    }

    $response = call_user_func_array([$this, $method], $invokeArgs);
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

  public function options()
  {
    throw new RestException('Not implemented', 405);
  }

  public function parseInput($data)
  {
    $result = json_decode($data, true);
    if($result === null) {
      throw new RestException('Input data contained invalid JSON');
    }
    return $result;
  }

  public function ok($message = 'OK')
  {
    return ['status_code'=>200, 'message'=>$message];
  }
}
