<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector;

use Slince\Collector\Exception\UnsupportedTypeException;
use Slince\Collector\Parser\ParserInterface;
use Slince\Collector\Parser\Repository;
use Symfony\Component\Filesystem\Filesystem;

class Collector
{
    /**
     * 入口链接
     * @var string
     */
    protected $rawEntranceUrl;

    /**
     * 要抓取的链接正则
     * @var array
     */
    protected $urlPatterns = [];

    /**
     * 黑名单链接
     * @var array
     */
    protected $blacklistUrls = [];

    /**
     * 白名单链接
     * @var array
     */
    protected $whitelistUrls = [];

    /**
     * 允许抓取的host，避免站外链接导致
     * 抓取过多页面
     * @var array
     */
    protected $allowedCaptureHosts = [];

    /**
     * 资源文件目录
     * @var string
     */
    protected $savePath;

    /**
     * 入口链接对象
     * @var Url
     */
    protected $entranceUrl;

    /**
     * 已经下载的链接正则
     * @var array
     */
    protected $downloadedUrlPatterns = [];

    /**
     * 已经下载的链接
     * @var array
     */
    protected $downloadedUrls = [];

    /**
     * 解析器
     * @var array
     */
    protected $parsers = [
        'Slince\Collector\Parser\HtmlParser',
        'Slince\Collector\Parser\CssParser',
        'Slince\Collector\Parser\ImageParser',
    ];
    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct($savePath, $rawEntranceUrl = null, array $urlPatterns = [])
    {
        $this->rawEntranceUrl = $rawEntranceUrl;
        $this->urlPatterns = $urlPatterns;
        $this->downloader = new Downloader();
        $this->filesystem = new Filesystem();
        $this->setSavePath($savePath);
    }

    /**
     * @param array $blacklistUrls
     */
    public function setBlacklistUrls($blacklistUrls)
    {
        $this->blacklistUrls = $blacklistUrls;
    }

    /**
     * @return array
     */
    public function getBlacklistUrls()
    {
        return $this->blacklistUrls;
    }

    /**
     * @return array
     */
    public function getWhitelistUrls()
    {
        return $this->whitelistUrls;
    }

    /**
     * @param string $rawEntranceUrl
     */
    public function setRawEntranceUrl($rawEntranceUrl)
    {
        $this->rawEntranceUrl = $rawEntranceUrl;
    }

    /**
     * @return string
     */
    public function getRawEntranceUrl()
    {
        return $this->rawEntranceUrl;
    }

    /**
     * @param array $urlPatterns
     */
    public function setUrlPatterns($urlPatterns)
    {
        $this->urlPatterns = $urlPatterns;
    }

    /**
     * @return array
     */
    public function getUrlPatterns()
    {
        return $this->urlPatterns;
    }

    /**
     * @param string $savePath
     */
    public function setSavePath($savePath)
    {
        $this->filesystem->mkdir($savePath);
        $this->savePath = $savePath;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
     * @param array $allowedCaptureHosts
     */
    public function setAllowedCaptureHosts($allowedCaptureHosts)
    {
        $this->allowedCaptureHosts = $allowedCaptureHosts;
    }

    /**
     * @return array
     */
    public function getAllowedCaptureHosts()
    {
        return $this->allowedCaptureHosts;
    }

    /**
     * @return array
     */
    public function getDownloadedUrls()
    {
        return $this->downloadedUrls;
    }

    /**
     * @return array
     */
    public function getDownloadedUrlPatterns()
    {
        return $this->downloadedUrlPatterns;
    }

    /**
     * @return Downloader
     */
    public function getDownloader()
    {
        return $this->downloader;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * 运行
     */
    function run()
    {
        $this->entranceUrl = Url::createFromUrl($this->rawEntranceUrl);
        $this->processUrl($this->entranceUrl);
    }

    /**
     * 处理链接
     * @param Url $url
     */
    protected function processUrl(Url $url)
    {
        var_dump(__LINE__);
        if ($this->filterUrl($url->getRawUrl())) {
            $content = $this->downloader->download($url);
            if ($content !== false) {
                $repository = $this->getParser($this->getContentType($url))->parse($content);
                $this->processRepository($repository);
            }
        }
    }

    /**
     * 处理内容
     * @param Repository $repository
     */
    protected function processRepository(Repository $repository)
    {
        $newFile = $this->savePath . DIRECTORY_SEPARATOR . $repository->getUrl()->getPath();
        $content = $repository->getContent();
        if ($repository->getUrl()->getHost() != $this->entranceUrl->getHost()) {
            if (in_array($repository->getUrl()->getHost(), $this->allowedCaptureHosts)) {
                $content = str_replace($repository->getUrl()->getHost(), $this->entranceUrl->getHost());
            }
        }
        $this->filesystem->dumpFile($newFile, $content);
        $this->downloadedUrls[] = $repository->getUrl()->getRawUrl();
        array_walk($repository->getImageUrls(), function ($url){
            $this->processUrl(Url::createFromUrl($url));
        });
        array_walk($repository->getCssUrls(), function ($url){
            $this->processUrl(Url::createFromUrl($url));
        });
        array_walk($repository->getScriptUrls(), function ($url){
            $this->processUrl(Url::createFromUrl($url));
        });
        array_walk($repository->getPageUrls(), function ($url){
            $this->processUrl(Url::createFromUrl($url));
        });
    }

    /**
     * 获取内容类型
     * @param Url $url
     * @param null $content
     * @return int
     */
    protected function getContentType(Url $url, $content = null)
    {
        $extension = pathinfo($url->getRawUrl(), PATHINFO_EXTENSION);
        if (empty($extension)) {
            return ParserInterface::TYPE_HTML;
        }
        $type = ParserInterface::TYPE_MEDIA;
        switch (strtolower($extension)) {
            case 'css':
                $type = ParserInterface::TYPE_CSS;
                break;
            case 'js':
                $type = ParserInterface::TYPE_SCRIPT;
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'bmp':
            case 'gif':
                $type = ParserInterface::TYPE_SCRIPT;
                break;
        }
        return $type;
    }

    /**
     * 判断链接是否要继续执行
     * @param $rawUrl
     * @return bool
     */
    protected function filterUrl($rawUrl)
    {
        if (in_array($rawUrl, $this->downloadedUrls)) {
            return false;
        }
        $pass = in_array($rawUrl, $this->whitelistUrls) || !in_array($rawUrl, $this->blacklistUrls);
        if ($pass && !empty($this->urlPatterns)) {
            foreach ($this->urlPatterns as $pattern) {
                //如果匹配到url模式，并且该模式还没有进行下载则通过
                if (preg_match($pattern, $rawUrl) && empty($this->downloadedUrlPatterns[$pattern])) {
                    $pass = true;
                    break;
                }
            }
        }
        return $pass;
    }

    /**
     * 获取内容类型解析器
     * @param $type
     * @return ParserInterface
     */
    function getParser($type)
    {
        $parser = null;
        foreach ($this->supportParsers as $parser) {
            if (in_array($type, call_user_func([
                $parser,
                'getSupportTypes'
            ]))) {
                if (! isset($this->parsers[$parser])) {
                    $this->parsers[$parser] = new $parser();
                }
                return $this->parsers[$parser];
            }
        }
        throw new UnsupportedTypeException('Unsupported content type');
    }
}