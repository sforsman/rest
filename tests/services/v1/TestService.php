<?php

namespace TestApi\v1;

use sforsman\Rest\AbstractJsonService;

class TestService extends AbstractJsonService
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