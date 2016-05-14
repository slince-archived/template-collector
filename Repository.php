<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector;

use Symfony\Component\Filesystem\Filesystem;

class Repository
{
    /**
     * 当前链接
     * @var Url
     */
    protected $url;

    /**
     * 内容类型
     * @var int
     */
    protected $contentType;
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

    /**
     * @var Filesystem
     */
    protected $filesystem;

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
        $this->pageUrls = $this->handleRawUrls($pageUrls);
    }

    /**
     * 获取页面链接
     * @return array
     */
    function getPageUrls()
    {
        return $this->pageUrls;
    }

    /**
     * 设置图片链接
     * @param array $imageUrls
     */
    function setImageUrls(array $imageUrls)
    {
        $this->imageUrls = $this->handleRawUrls($imageUrls);
    }

    /**
     * 获取图片链接
     * @return array
     */
    function getImageUrls()
    {
        return $this->imageUrls;
    }

    /**
     * 设置样式链接
     * @param array $cssUrls
     */
    function setCssUrls(array $cssUrls)
    {
        $this->cssUrls = $this->handleRawUrls($cssUrls);
    }

    /**
     * 获取样式链接
     * @return array
     */
    function getCssUrls()
    {
        return $this->cssUrls;
    }

    /**
     * 设置脚本链接
     * @param array $scriptUrls
     */
    function setScriptUrls(array $scriptUrls)
    {
        $this->scriptUrls = $this->handleRawUrls($scriptUrls);
    }

    /**
     * 获取脚本链接
     * @return array
     */
    function getScriptUrls()
    {
        return $this->scriptUrls;
    }

    /**
     * 设置当前连接
     * @param mixed $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
    }

    /**
     * 获取当前链接
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param int $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return int
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * 批量处理原生url
     * @param $rawUrls
     * @return array
     */
    protected function handleRawUrls($rawUrls)
    {
        $rawUrls = array_unique($rawUrls);
        $urls = [];
        foreach ($rawUrls as $rawUrl) {
            if (!empty($rawUrl)) {
                $urls[] = $this->handleRawUrl($rawUrl);
            }
        }
        return $urls;
    }

    /**
     * 处理原生url
     * @param $rawUrl
     * @return Url
     */
    protected function handleRawUrl($rawUrl)
    {
        if (strpos($rawUrl, 'http') !== false ||substr($rawUrl, 0, 2) == '//') {
            $newRawUrl = $rawUrl;
        } else {
            if ($rawUrl{0} !== '/') {
                if ($this->url->getParameter('extension') == '') {
                    $pathname = rtrim($this->url->getPath(), '') . '/' . $rawUrl;
                } else {
                    $pathname = dirname($this->url->getPath()) . '/' . $rawUrl;
                }
            } else {
                $pathname = $rawUrl;
            }
            $newRawUrl = $this->url->getOrigin() . $pathname;;
        }
        $url =  Url::createFromUrl($newRawUrl);
        $url->setRawUrl($rawUrl);
        return $url;
    }
}