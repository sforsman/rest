<?php

namespace sforsman\Rest;

interface ServiceInterface
{
  public function invoke($request_method, array $args);
}