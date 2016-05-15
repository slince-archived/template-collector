<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Collector\Collector;
use Slince\Event\Event;

$collector = new Collector(__DIR__ . '/html', 'http://demo.sc.chinaz.com/Files/DownLoad/moban/201604/moban1178/index.html');
$collector->getDispatcher()->bind(Collector::EVENT_CAPTURE_URL_REPOSITORY, function(Event $event){
    $repository = $event->getArgument('repository');
    echo 'Begin Capture ' , $repository->getUrl()->getUrlString(), "\r\n";
});
$collector->getDispatcher()->bind(Collector::EVENT_CAPTURED_URL_REPOSITORY, function(Event $event){
    $repository = $event->getArgument('repository');
    echo $repository->getUrl()->getUrlString() . " Captured OK!\r\n";
});
$collector->run();