<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

class ImageParser extends Parser
{
    function extractPageUrls($content)
    {
        return [];
    }

    function extractImageUrls($content)
    {
        return [];
    }

    function extractCssUrls($content)
    {
        return [];
    }

    function extractScriptUrls($content)
    {
        return [];
    }

    static function getSupportTypes()
    {
        return [ParserInterface::TYPE_IMAGE, ParserInterface::TYPE_SCRIPT, ParserInterface::TYPE_MEDIA];
    }
}