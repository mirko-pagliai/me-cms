<?php
namespace TestPlugin\View\Helper;

use Cake\View\Helper;

class MenuHelper extends Helper
{
    public $helpers = ['Html' => ['className' => 'MeTools.Html']];

    //@codingStandardsIgnoreLine
    public function _invalidMethod()
    {
    }

    //@codingStandardsIgnoreLine
    public function __otherInvalidMethod()
    {
    }

    public function articles()
    {
        $links = [
            ['First link', '/'],
            ['Second link', '/'],
        ];

        return [$links, 'First menu', ['icon' => 'home'], ['Articles']];
    }

    public function other_items()
    {
        $links = [
            ['Third link', '/'],
            ['Fourth link', '/'],
        ];

        return [$links, 'Second menu', ['icon' => 'flag'], ['OtherItems']];
    }
}
