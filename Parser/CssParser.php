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
     * 由于字体链接与图片链接处理方式相同，谷此处一并获取
     * @param $content
     * @return array
     * jpg|jpeg|gif|png|bmp|svg|ttf|eot|woff|otf|woff2
     */
    function extractImageUrls($content)
    {
        preg_match_all("/url\s*\((.*\.(?:jpg|jpeg|gif|png|bmp|svg|ttf|eot|woff|otf|woff2).*)\)/Ui", $content, $matches);
        $urls = empty($matches[1]) ? [] : $matches[1];
        array_walk($urls, function (&$url) {
            $url = trim($url, '"\'');
        });
        return $urls;
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    function extractCssUrls($content)
    {
        preg_match_all("/url\s*\((.*\.css.*)\)/Ui", $content, $matches);
        $urls = empty($matches[1]) ? [] : $matches[1];
        array_walk($urls, function (&$url) {
            $url = trim($url, '"\'');
        });
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