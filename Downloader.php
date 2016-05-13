<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector;

class Downloader
{
    /**
     * 下载链接内容
     * @param $url
     * @return string
     */
    function download($url)
    {
        return @file_get_contents($url);
    }
}