<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

class ScriptParser extends Parser
{
    /**
     * 从内容里提取所有的页面链接
     * @param $content
     * @return array
     */
    function extractPageUrls($content)
    {
        return [];
    }

    /**
     * 从内容里提取所有的图片链接链接
     * @param $content
     * @return array
     */
    function extractImageUrls($content)
    {
        return [];
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    function extractCssUrls($content)
    {
        return [];
    }

    /**
     * 从内容里提取所有的脚本链接
     * @param $content
     * @return array
     */
    function extractScriptUrls($content)
    {
        if (preg_match('#require\(\[([\s\S]+)\]#mU', $content, $matches)) {
            $urls = array_map(function($urlFragment){
                $urlFragment = preg_replace('#\s#', '', $urlFragment);
                return "http://www.chinabrands.com/static/scripts.src/" . trim($urlFragment, "'") . '.js';
            }, explode(',', $matches[1]));
        } else {
            $urls = [];
        }
        return $urls;
    }

    public static function getSupportTypes()
    {
        return [ParserInterface::TYPE_SCRIPT];
    }
}