<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector;

use Slince\Collector\Exception\UnsupportedTypeException;
use Slince\Collector\Parser\ParserInterface;
use Slince\Event\Dispatcher;
use Slince\Event\Event;
use Symfony\Component\Filesystem\Filesystem;

class Collector
{
    /**
     * 过滤url事件
     * @var string
     */
    const EVENT_FILTER_URL = 'filterUrl';

    /**
     * 采集url内容事件
     * @var string
     */
    const EVENT_CAPTURE_URL_REPOSITORY = 'captureUrlRepository';

    /**
     * 采集完毕url内容事件
     * @var string
     */
    const EVENT_CAPTURED_URL_REPOSITORY = 'capturedUrlRepository';

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
     * 是否只抓取指定链接规则
     * @var bool
     */
    protected $onlyCaptureUrlPatterns = true;

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
    protected $parsers = [];

    /**
     * 支持的解析器
     * @var array
     */
    protected $supportedParsers = [
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

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct($savePath, $rawEntranceUrl = null, array $urlPatterns = [])
    {
        $this->rawEntranceUrl = $rawEntranceUrl;
        $this->urlPatterns = $urlPatterns;
        $this->downloader = new Downloader();
        $this->filesystem = new Filesystem();
        $this->dispatcher = new Dispatcher();
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
     * @param array $whitelistUrls
     */
    public function setWhitelistUrls($whitelistUrls)
    {
        $this->whitelistUrls = $whitelistUrls;
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
     * @param boolean $onlyCaptureUrlPatterns
     */
    public function setOnlyCaptureUrlPatterns($onlyCaptureUrlPatterns)
    {
        $this->onlyCaptureUrlPatterns = $onlyCaptureUrlPatterns;
    }

    /**
     * 是否只采集路由规则里的链接
     * @return bool
     */
    public function getOnlyCaptureUrlPatterns()
    {
        return $this->onlyCaptureUrlPatterns;
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
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * 运行
     */
    public function run()
    {
        $this->entranceUrl = Url::createFromUrl($this->rawEntranceUrl);
        $this->processUrl($this->entranceUrl);
    }


    /**
     * 处理链接，采集处理入口
     * @param Url $url
     * @param Repository|null $parentRepository
     */
    protected function processUrl(Url $url)
    {
        if ($this->filterUrl($url)) {
            $content = $this->downloader->download($url);
            if ($content !== false) {
                //获取该url对应的内容类型
                $contentType = $this->getContentType($url, $content);
                //调用内容解析器提取内容包
                $repository = $this->getParser($contentType)->parse($url, $content);
                $repository->setContentType($contentType); //设置内容类型
                $this->processRepository($repository);
            }
        }
    }

    /**
     * 处理repository
     * @param Repository $repository
     */
    protected function processRepository(Repository $repository)
    {
        //静态资源允许跨host
        if ($repository->getUrl()->getHost() != $this->entranceUrl->getHost()) {
            if (in_array($repository->getContentType(), [
                    ParserInterface::TYPE_CSS,
                    ParserInterface::TYPE_IMAGE,
                    ParserInterface::TYPE_SCRIPT,
                    ParserInterface::TYPE_MEDIA
                ])
                && in_array($repository->getUrl()->getHost(), $this->allowedCaptureHosts)
            ) {
                //静态资源所属父级repository的content要进行替换
                $parentRepository = $repository->getUrl()->getParameter('repository');
                if (!is_null($parentRepository)) {
                    $parentRepository->setContent(str_replace(
                        $repository->getUrl()->getHost(),
                        $this->entranceUrl->getHost(),
                        $parentRepository->getContent()
                    ));
                }
            } else {
                return false;
            }
        }
        /*
         * 开始采集当前repository，优先采集当页所有的资源文件再采集页面本身，以免因为资源文件判断会对
         * 页面本身内容的修改调整
         */
        $this->dispatcher->dispatch(self::EVENT_CAPTURE_URL_REPOSITORY, new Event(
            self::EVENT_CAPTURE_URL_REPOSITORY, $this, [
            'repository' => $repository
        ]));
        foreach ($repository->getImageUrls() as $url) {
            $this->processUrl($url);
        }
        foreach ($repository->getCssUrls() as $url) {
            $this->processUrl($url);
        }
        foreach ($repository->getScriptUrls() as $url) {
            $this->processUrl($url);
        }
        $newFile = $this->savePath . DIRECTORY_SEPARATOR . $repository->getUrl()->getPath();
        //如果链接没有扩展名，并且也没有预定义文件名则生成随机文件名
        if ($repository->getUrl()->getParameter('extension') == '') {
            $filename = $repository->getUrl()->getParameter('filename');
            if (!$filename) {
                $filename = $this->generateFilename();
            }
            $newFile = rtrim($newFile, '/') . '/' . $filename . '.html';
        }
        $this->filesystem->dumpFile($newFile, $repository->getContent());
        $this->downloadedUrls[] = $repository->getUrl()->getRawUrl();
        //页面内容采集完毕
        $this->dispatcher->dispatch(self::EVENT_CAPTURED_URL_REPOSITORY, new Event(
            self::EVENT_CAPTURED_URL_REPOSITORY, $this, [
            'repository' => $repository
        ]));
        //采集当页的其它链接，其它链接的采集不属于一个采集周期
        foreach ($repository->getPageUrls() as $url) {
            $this->processUrl($url);
        }
    }

    /**
     * 生存一个随机的文件名
     * @return string
     */
    protected function generateFilename()
    {
        $string = 'abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($string), 0, 10);
    }

    /**
     * 获取内容类型
     * @param Url $url
     * @param null $content
     * @return int
     */
    protected function getContentType(Url $url, $content = null)
    {
        $extension = pathinfo($url->getPath(), PATHINFO_EXTENSION);
        if (empty($extension)) {
            $type = ParserInterface::TYPE_HTML;
        } else {
            switch (strtolower($extension)) {
                case 'htm':
                case 'html':
                    $type = ParserInterface::TYPE_HTML;
                    break;
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
                    $type = ParserInterface::TYPE_IMAGE;
                    break;
                default:
                    $type = ParserInterface::TYPE_MEDIA;
            }
        }
        $url->setParameter('extension', $extension);
        return $type;
    }

    /**
     * 判断链接是否要继续执行
     * @param Url $url
     * @return bool
     */
    protected function filterUrl(Url $url)
    {
        //已经下载的链接不再处理
        if (in_array($url->getRawUrl(), $this->downloadedUrls)) {
            return false;
        }
        //触发过滤url事件
        $this->dispatcher->dispatch(self::EVENT_FILTER_URL, new Event(
            self::EVENT_FILTER_URL, $this, [
            'url' => $url
        ]));
        $pass = true;
        if (!in_array($url->getRawUrl(), $this->whitelistUrls)) {
            if (in_array($url->getRawUrl(), $this->blacklistUrls) ||
                (preg_match("/^\s*(?:#|mailto|javascript)/", $url->getRawUrl()))
            ) {
                $pass = false;
            }
        }
        if ($pass && !empty($this->urlPatterns)) {
            //如果设置只抓取符合抓取规则的链接，则必须要通过规则检查
            if ($this->onlyCaptureUrlPatterns) {
                $pass = false;
            }
            foreach ($this->urlPatterns as $filename => $pattern) {
                //如果匹配到url模式，并且该模式还没有进行下载则通过
                if (preg_match($pattern, $url->getRawUrl()) && empty($this->downloadedUrlPatterns[$pattern])) {
                    //将命令的文件名存入url
                    $url->setParameter('filename', $filename);
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
    public function getParser($type)
    {
        if (isset($this->parsers[$type])) {
            return $this->parsers[$type];
        }
        foreach ($this->supportedParsers as $parser) {
            if (in_array($type, call_user_func([
                $parser,
                'getSupportTypes'
            ]))) {
                $this->parsers[$type] = new $parser();
                return $this->parsers[$type];
            }
        }
        throw new UnsupportedTypeException('Unsupported content type');
    }
}