<?php

namespace sforsman\Rest;

use League\Event\Emitter;

interface ServiceInterface
{
  public function __construct(Emitter $emitter);
  public function invoke($request_method, array $args);
}