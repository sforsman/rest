<?php

namespace sforsman\Rest;

use Symfony\Component\HttpFoundation\Request;

interface ServiceInterface
{
  public function invoke($request_method, array $args, Request $request);
}