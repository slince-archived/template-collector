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
     * @var Url
     */
    protected $url;
    protected $content;
    protected $pageUrls = [];
    protected $imageUrls = [];
    protected $cssUrls = [];
    protected $scriptUrls = [];

    function __construct($url, $content, array $pageUrls = [], array $imageUrls = [], array $cssUrls = [], array $scriptUrls = [])
    {
        $this->url = Url::createFromUrl($url);
        $this->content = $content;
        $this->pageUrls = $pageUrls;
        $this->imageUrls = $imageUrls;
        $this->cssUrls = $cssUrls;
        $this->scriptUrls = $scriptUrls;
    }

    function setContent($content)
    {
        $this->content = $content;
    }

    function getContent()
    {
        return $this->content;
    }

    function setPageUrls(array $pageUrls)
    {
        $this->pageUrls = $pageUrls;
    }

    function getPageUrls()
    {
        return array_unique($this->pageUrls);
    }

    function setImageUrls(array $imageUrls)
    {
        $this->imageUrls = $imageUrls;
    }

    function getImageUrls()
    {
        return array_unique($this->imageUrls);
    }

    function setCssUrls(array $cssUrls)
    {
        $this->cssUrls = $cssUrls;
    }

    function getCssUrls()
    {
        return array_unique($this->cssUrls);
    }

    function setScriptUrls(array $scriptUrls)
    {
        $this->scriptUrls = $scriptUrls;
    }

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