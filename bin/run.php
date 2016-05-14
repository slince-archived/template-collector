<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Collector\Collector;

$collector = new Collector(__DIR__ . '/html', 'http://demo.sc.chinaz.com/Files/DownLoad/moban/201604/moban1178/index.html');
$collector->run();