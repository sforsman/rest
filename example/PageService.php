<?php

use sforsman\Rest\AbstractJsonService;

class PageService extends AbstractJsonService
{
  public function all()
  {
    return ['message'=> 'Hi, world!'];
  }
}