<?php
/**
 * slince template collector library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Collector\Parser;

use Slince\Collector\Url;

interface ParserInterface
{
    /**
     * 内容类型，html
     * @var int
     */
    const TYPE_HTML = 0;

    /**
     * 内容类型，image
     * @var int
     */
    const TYPE_IMAGE = 1;

    /**
     * 内容类型，css
     * @var int
     */
    const TYPE_CSS = 2;

    /**
     * 内容类型，script
     * @var int
     */
    const TYPE_SCRIPT = 3;

    /**
     * 内容类型，media
     * @var int
     */
    const TYPE_MEDIA = 4;

    /**
     * 解析内容
     * @param Url $url
     * @param $content
     * @return Repository
     */
    function parse(Url $url, $content);

    static function getSupportTypes();
}