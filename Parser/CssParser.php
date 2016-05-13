<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

class CssParser extends Parser
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
        $urls = preg_match("/url\s*\((.*)\)/i", function($matches){
            return trim($matches[1], '\'"');
        }, $content);
        return $urls;
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    function extractCssUrls($content)
    {
        $urls = preg_match("/url\s*\((.*\.css)\)/", function($matches){
            return trim($matches[1], '\'"');
        }, $content);
        return $urls;
    }

    /**
     * 从内容里提取所有的脚本链接
     * @param $content
     * @return array
     */
    function extractScriptUrls($content)
    {
        return [];
    }

    /**
     * 获取当前解析器支持的类型
     * @return array
     */
    static function getSupportTypes()
    {
        return [ParserInterface::TYPE_CSS];
    }
}