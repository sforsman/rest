<?php

namespace TestApi\v1;

use sforsman\Rest\AbstractJsonService;

class SubentityService extends AbstractJsonService
{
  public function get($id, $subentity = null)
  {
    if($subentity !== null) {
      return ['id'=>$id, 'subentity'=>$subentity];
    } else {
      return ['id'=>$id];
    }
  }

  public function all()
  {
    return ['ids'=>[1,2,3]];
  }

  public function post($data, $subentity = null)
  {
    if($subentity !== null) {
      return ['id'=>1000, 'data'=>$data, 'subentity'=>$subentity];
    } else {
      return ['id'=>1000, 'data'=>$data];      
    }
  }

  public function put($id, $data, $subentity = null)
  {
    if($subentity !== null) {
      return ['id'=>$id, 'data'=>$data, 'subentity'=>$subentity];
    } else {
      return ['id'=>$id, 'data'=>$data];
    }
  }

  public function delete($id, $subentity = null)
  {
    if($subentity !== null) {
      return ['id'=>$id, 'subentity'=>$subentity];
    } else {
      return ['id'=>$id];
    }
  }

  public function patch($id, $data, $subentity = null)
  {
    if($subentity !== null) {
      return ['id'=>$id, 'data'=>$data, 'subentity'=>$subentity];
    } else {
      return ['id'=>$id, 'data'=>$data];
    }
  }
}