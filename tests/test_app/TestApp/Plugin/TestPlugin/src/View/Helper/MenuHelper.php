<?php
declare(strict_types=1);

namespace TestPlugin\View\Helper;

use MeCms\View\Helper\AbstractMenuHelper;

class MenuHelper extends AbstractMenuHelper
{
    public $helpers = ['MeTools.Html'];

    public function _invalidMethod()
    {
    }

    public function __otherInvalidMethod()
    {
    }

    public function articles(): array
    {
        $links = [
            ['First link', '/'],
            ['Second link', '/'],
        ];

        return [$links, 'First menu', ['icon' => 'home'], ['Articles']];
    }

    public function other_items(): array
    {
        $links = [
            ['Third link', '/'],
            ['Fourth link', '/'],
        ];

        return [$links, 'Second menu', ['icon' => 'flag'], ['OtherItems']];
    }
}
