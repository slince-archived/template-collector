<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

use Slince\Collector\Url;

class Repository
{
    /**
     * 当前链接
     * @var Url
     */
    protected $url;

    /**
     * 当前连接下载的内容
     * @var string
     */
    protected $content;

    /**
     * 页面链接
     * @var array
     */
    protected $pageUrls = [];

    /**
     * 图片链接
     * @var array
     */
    protected $imageUrls = [];

    /**
     * 样式链接
     * @var array
     */
    protected $cssUrls = [];

    /**
     * 脚本链接
     * @var array
     */
    protected $scriptUrls = [];

    function __construct(Url $url, $content, array $pageUrls = [], array $imageUrls = [], array $cssUrls = [], array $scriptUrls = [])
    {
        $this->url = $url;
        $this->content = $content;
        $this->pageUrls = $pageUrls;
        $this->imageUrls = $imageUrls;
        $this->cssUrls = $cssUrls;
        $this->scriptUrls = $scriptUrls;
    }

    /**
     * 设置内容
     * @param $content
     */
    function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * 获取内容
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * 设置页面链接
     * @param array $pageUrls
     */
    function setPageUrls(array $pageUrls)
    {
        $this->pageUrls = $pageUrls;
    }

    /**
     * 获取页面链接
     * @return array
     */
    function getPageUrls()
    {
        return array_unique($this->pageUrls);
    }

    /**
     * 设置图片链接
     * @param array $imageUrls
     */
    function setImageUrls(array $imageUrls)
    {
        $this->imageUrls = $imageUrls;
    }

    /**
     * 获取图片链接
     * @return array
     */
    function getImageUrls()
    {
        return array_unique($this->imageUrls);
    }

    /**
     * 设置样式链接
     * @param array $cssUrls
     */
    function setCssUrls(array $cssUrls)
    {
        $this->cssUrls = $cssUrls;
    }

    /**
     * 获取样式链接
     * @return array
     */
    function getCssUrls()
    {
        return array_unique($this->cssUrls);
    }

    /**
     * 设置脚本链接
     * @param array $scriptUrls
     */
    function setScriptUrls(array $scriptUrls)
    {
        $this->scriptUrls = $scriptUrls;
    }

    /**
     * 获取脚本链接
     * @return array
     */
    function getScriptUrls()
    {
        return array_unique($this->scriptUrls);
    }

    /**
     * @param mixed $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}