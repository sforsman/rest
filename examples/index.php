<?php

require __DIR__ . '/vendor/autoload.php';

use sforsman\Rest\Server;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use League\Event\Emitter;

class PageController extends AbstractJsonController
{
  public function all()
  {
    echo 'Hi, world!';
  }
}

$log = new Logger('API');
$log->pushHandler(new StreamHandler('/tmp/api_log.txt', Logger::WARNING));

$emitter = new Emitter();
$emitter->addListener('dispatch.begin', CallbackListener::fromCallback(function (AbstractEvent $event, $param = null) use ($log) {
  $log->addNotice(serialize($param));
}));


$api = new Server();

$api->register('page', PageController::class);

$api->run();
