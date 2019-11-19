<?php
declare(strict_types=1);
namespace TestPlugin\Utility;

use MeCms\Utility\SitemapBuilder;

class Sitemap extends SitemapBuilder
{
    protected static function _ignoredMethod()
    {
    }

    public static function urlMethod1()
    {
    }

    public static function urlMethod2()
    {
    }
}
