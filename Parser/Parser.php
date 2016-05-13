<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

use Slince\Collector\Url;

abstract class Parser implements ParserInterface
{

    /**
     * 解析内容
     * @param Url $url
     * @param $content
     * @return Repository
     */
    function parse(Url $url, $content)
    {
        $repository = $this->createRepository($url, $content);
        $repository->setContent($content);
        $repository->setPageUrls($this->extractPageUrls($content));
        $repository->setImageUrls($this->extractImageUrls($content));
        $repository->setCssUrls($this->extractCssUrls($content));
        $repository->setScriptUrls($this->extractScriptUrls($content));
        return $repository;
    }

    abstract protected function extractPageUrls($content);

    abstract protected function extractImageUrls($content);

    abstract protected function extractCssUrls($content);

    abstract protected function extractScriptUrls($content);

    protected function createRepository(Url $url, $content)
    {
        return new Repository($url, $content);
    }
}

