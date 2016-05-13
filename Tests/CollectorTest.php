<?php
namespace Slince\Collector\Tests;

use Slince\Collector\Collector;

class CollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collector
     */
    protected $collector;

    function setUp()
    {
        $this->collector = new Collector(__DIR__ . '/html');
    }

    function testRun()
    {
        $this->collector->setRawEntranceUrl('http://minimal.ondrejsvestka.cz/1-3-3/index.html');
        $this->collector->run();
        $this->assertNotEmpty('1');
    }
}