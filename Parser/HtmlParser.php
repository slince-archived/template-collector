<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

use HtmlParser\ParserDom;

class HtmlParser extends Parser
{
    /**
     * @var ParserDom
     */
    protected $domParser;

    function __construct()
    {
        $this->domParser = new ParserDom();
    }

    /**
     * 从内容里提取所有的页面链接
     * @param $content
     * @return array
     */
    function extractPageUrls($content)
    {
        $this->domParser->load($content);
        $aNodes = $this->domParser->find('a');
        return array_map(function($aNode){
            return $aNode->getAttr('href');
        }, $aNodes);
    }

    /**
     * 从内容里提取所有的图片链接链接
     * @param $content
     * @return array
     */
    function extractImageUrls($content)
    {
        $this->domParser->load($content);
        $imgNodes = $this->domParser->find('img');
        return array_map(function($imgNode){
            return $imgNode->getAttr('src');
        }, $imgNodes);
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    function extractCssUrls($content)
    {
        $this->domParser->load($content);
        $cssNodes = $this->domParser->find("link[rel='stylesheet']");
        return array_map(function($cssNode){
            return $cssNode->getAttr('href');
        }, $cssNodes);
    }

    /**
     * 从内容里提取所有的脚本链接
     * @param $content
     * @return array
     */
    function extractScriptUrls($content)
    {
        $this->domParser->load($content);
        $scriptNodes = $this->domParser->find('script');
        $urls = array_map(function($scriptNode){
            return $scriptNode->getAttr('src');
        }, $scriptNodes);
        if (preg_match("#pagesFile\('(.*)'\)#", $content, $matches)) {
            $jsUrls =  array_map(function($urlName){
                $urlName = trim($urlName);
                return "http://www.chinabrands.com/static/scripts.src/pages/{$urlName}.js";
            }, explode(',', $matches[1]));
        } else {
            $jsUrls = [];
        }
        return array_merge($urls, $jsUrls);
    }

    /**
     * 获取当前解析器支持的类型
     * @return array
     */
    static function getSupportTypes()
    {
        return [ParserInterface::TYPE_HTML];
    }
}