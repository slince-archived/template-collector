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
    const EVENT_FILTERED_URL = 'filteredUrl';

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
        'Slince\Collector\Parser\ScriptParser',
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

    /**
     * 运行
     */
    public function run()
    {
        $this->entranceUrl = Url::createFromUrl($this->rawEntranceUrl);
        $this->processUrl($this->entranceUrl, true); //入口链接直接通过filterPageUrl检查
    }

    /**
     * 处理链接，采集处理入口
     * @param Url $url
     * @param boolean $passFilter
     */
    protected function processUrl(Url $url, $passFilter = false)
    {
        if ($this->filterUrl($url)) {
            $content = $this->downloader->download($url);
            if ($content !== false) {
                //获取该url对应的内容类型
                $contentType = $this->getContentType($url, $content);
                //page url要进行专属过滤
                if ($contentType != ParserInterface::TYPE_HTML || $passFilter || $this->filterPageUrl($url)) {
                    //调用内容解析器提取内容包
                    $repository = $this->getParser($contentType)->parse($url, $content);
                    $repository->setContentType($contentType); //设置内容类型
                    $this->processRepository($repository);
                }
            }
        }
    }

    /**
     * 处理repository
     * @param Repository $repository
     * @return boolean
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
                    $parentRepository->setContent(preg_replace(
                        "#(?:http)?s?:?(?://)?{$repository->getUrl()->getHost()}#",
                        '',
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
        foreach ($repository->getCssUrls() as $url) {
            $this->processUrl($url);
        }
        foreach ($repository->getScriptUrls() as $url) {
            $this->processUrl($url);
        }
        foreach ($repository->getImageUrls() as $url) {
            $this->processUrl($url);
        }
        $newFile =  $this->generateFilename($repository);
        $this->filesystem->dumpFile($newFile, $repository->getContent());
        //当前连接记录为已下载链接，符合采集规则的链接，采集规则要记录为已下载
        $this->downloadedUrls[] = $repository->getUrl()->getRawUrl();
        if ($pattern = $repository->getUrl()->getParameter('pattern')) {
            $this->downloadedUrlPatterns[] = $pattern;
        }
        //页面内容采集完毕
        $this->dispatcher->dispatch(self::EVENT_CAPTURED_URL_REPOSITORY, new Event(
            self::EVENT_CAPTURED_URL_REPOSITORY, $this, [
            'repository' => $repository
        ]));
        //采集当页的其它链接，其它链接的采集不属于一个采集周期
        foreach ($repository->getPageUrls() as $url) {
            $this->processUrl($url);
        }
        return true;
    }

    /**
     * 生成文件名
     * @param Repository $repository
     * @return string
     */
    protected function generateFileName(Repository $repository)
    {
        $newFile = rtrim($this->savePath . DIRECTORY_SEPARATOR . $repository->getUrl()->getPath(), '\\/');
        if ($repository->getUrl()->getParameter('extension') == '') {
            $filename = $repository->getUrl()->getParameter('filename');
            if (!$filename) {
                $unavailable = true;
                $index = 0;
                while ($unavailable) {
                    $newFilePath = $newFile . "/index{$index}.html";
                    if (!$this->filesystem->exists($newFilePath)) {
                        $unavailable = false;
                        $newFile = $newFilePath;
                    } else {
                        $index ++;
                    }
                }
            } else {
                $newFile .= "/{$filename}.html";
            }
        }
        return $newFile;
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
        switch (strtolower($extension)) {
            case '':
            case 'php':
            case 'jsp':
            case 'py':
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
        $pass = true;
        //不在白名单里的链接要进行合法检查
        if (!in_array($url->getRawUrl(), $this->whitelistUrls)) {
            if (in_array($url->getRawUrl(), $this->blacklistUrls) ||
                (preg_match("/^\s*(?:#|mailto|javascript)/", $url->getRawUrl()))
            ) {
                $pass = false;
            }
        }
        //触发过滤结束url事件
        $this->dispatcher->dispatch(self::EVENT_FILTERED_URL, new Event(
            self::EVENT_FILTERED_URL, $this, [
            'url' => $url,
            'pass' => $pass
        ]));
        return $pass;
    }

    /**
     * 页面url专属判断
     * @param Url $url
     * @return bool
     */
    protected function filterPageUrl(Url $url)
    {
        //host和入口链接host不一致的不抓取
        if ($url->getHost() == $this->entranceUrl->getHost()) {
            $pass = true;
            if (!empty($this->urlPatterns)) {
                //如果设置只抓取符合抓取规则的链接，则必须要通过规则检查
                if ($this->onlyCaptureUrlPatterns) {
                    $pass = false;
                }
                foreach ($this->urlPatterns as $filename => $pattern) {
                    //如果匹配到url模式，并且该模式还没有进行下载则通过
                    if (preg_match($pattern, $url->getRawUrl()) && !in_array($pattern, $this->downloadedUrlPatterns)) {
                        //将命令的文件名存入url
                        $url->setParameter('filename', $filename);
                        $url->setParameter('pattern', $pattern);
                        $pass = true;
                        break;
                    }
                }
            }
        } else {
            $pass = false;
        }
        return $pass;
    }
}