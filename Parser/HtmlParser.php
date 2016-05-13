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

    function extractPageUrls($content)
    {
        $this->domParser->load($content);
        $aNodes = $this->domParser->find('a');
        return array_map(function($aNode){
            return $aNode->getAttr('href');
        }, $aNodes);
    }

    function extractImageUrls($content)
    {
        $this->domParser->load($content);
        $imgNodes = $this->domParser->find('img');
        return array_map(function($imgNode){
            return $imgNode->getAttr('href');
        }, $imgNodes);
    }

    function extractCssUrls($content)
    {
        $this->domParser->load($content);
        $cssNodes = $this->domParser->find("link[rel='stylesheet']");
        return array_map(function($cssNode){
            return $cssNode->getAttr('href');
        }, $cssNodes);
    }

    function extractScriptUrls($content)
    {
        $this->domParser->load($content);
        $scriptNodes = $this->domParser->find('script');
        return array_map(function($scriptNode){
            return $scriptNode->getAttr('href');
        }, $scriptNodes);
    }

    static function getSupportTypes()
    {
        return [ParserInterface::TYPE_HTML];
    }
}