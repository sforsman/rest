<?php

require __DIR__ . '/vendor/autoload.php';
require 'PageService.php';

use sforsman\Rest\Server;
use sforsman\Rest\AbstractJsonService;
use League\Event\Emitter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('API');
$log->pushHandler(new StreamHandler('/tmp/api_log.txt', Logger::WARNING));

$emitter = new Emitter();
$emitter->addListener('dispatch', CallbackListener::fromCallback($callback));

$callback = function (AbstractEvent $event, $param = null) use ($log) {
  // In the real world, you would (for an example) validate OAuth2 headers here
  $log->addNotice(serialize($param));
};

$api = new Server($emitter);
$api->register('page', PageService::class);
$api->run();