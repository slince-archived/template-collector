<?php
include __DIR__ . '/../vendor/autoload.php';

use Slince\Collector\Collector;

$collector = new Collector(__DIR__ . '/html', 'http://minimal.ondrejsvestka.cz/1-3-3/index.html');
$collector->run();