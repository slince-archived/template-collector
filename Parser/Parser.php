<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

use Slince\Collector\Url;
use Slince\Collector\Repository;
use Symfony\Component\Filesystem\Filesystem;

abstract class Parser implements ParserInterface
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * 解析内容
     * @param Url $url
     * @param $content
     * @return Repository
     */
    function parse(Url $url, $content)
    {
        $repository = $this->createRepository($url, $content);
        $repository->setPageUrls($this->extractPageUrls($content));
        $repository->setImageUrls($this->extractImageUrls($content));
        $repository->setCssUrls($this->extractCssUrls($content));
        $repository->setScriptUrls($this->extractScriptUrls($content));
        return $repository;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * 从内容里提取所有的页面链接
     * @param $content
     * @return array
     */
    abstract protected function extractPageUrls($content);

    /**
     * 从内容里提取所有的图片链接链接
     * @param $content
     * @return array
     */
    abstract protected function extractImageUrls($content);

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    abstract protected function extractCssUrls($content);

    /**
     * 从内容里提取所有的脚本链接
     * @param $content
     * @return array
     */
    abstract protected function extractScriptUrls($content);

    /**
     * 创建内容解析对象
     * @param Url $url
     * @param $content
     * @return Repository
     */
    protected function createRepository(Url $url, $content)
    {
        $repository = new Repository($url, $content);
        $repository->setFilesystem($this->filesystem);
        return $repository;
    }
}

