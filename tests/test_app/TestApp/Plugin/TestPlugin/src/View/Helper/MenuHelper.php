<?php
declare(strict_types=1);

namespace TestPlugin\View\Helper;

use Cake\View\Helper;

class MenuHelper extends Helper
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
