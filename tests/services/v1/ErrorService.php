<?php

namespace TestApi\v1;

use \Exception;
use sforsman\Rest\AbstractJsonService;
use sforsman\Rest\RestException;

class ErrorService extends AbstractJsonService
{
  public function get($id)
  {
    if(!preg_match("|^[0-9]+$|", $id)) {
      throw new RestException('Bad id');
    } else {
      throw new Exception('Failed getting ' . $id);
    }
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
