<?php
declare(strict_types=1);

namespace TestPluginTwo\View\Helper;

use Cake\View\Helper;

class MenuHelper extends Helper
{
    public $helpers = ['MeTools.Html'];

    /**
     * Bad `MenuHelper` method. It returns only one argument
     * @return array
     * @see \MeCms\Test\TestCase\View\Helper\MenuBuilderHelperTest::testGenerate()
     */
    public function badArticles(): array
    {
        $links = [
            ['First link', '/'],
            ['Second link', '/'],
        ];

        return [$links];
    }
}
