<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector;

class Downloader
{
    function download($url)
    {
        return @file_get_contents($url);
    }
}