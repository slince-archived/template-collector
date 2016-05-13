<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

class CssParser extends Parser
{
    function extractPageUrls($content)
    {
        return [];
    }

    function extractImageUrls($content)
    {
        $urls = preg_match("/url\s*\((.*)\)/i", function($matches){
            return trim($matches[1], '\'"');
        }, $content);
        return $urls;
    }

    function extractCssUrls($content)
    {
        $urls = preg_match("/url\s*\((.*\.css)\)/", function($matches){
            return trim($matches[1], '\'"');
        }, $content);
        return $urls;
    }

    function extractScriptUrls($content)
    {
        return [];
    }

    static function getSupportTypes()
    {
        return [ParserInterface::TYPE_CSS];
    }
}