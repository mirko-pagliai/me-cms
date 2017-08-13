<?php
namespace TestPlugin\View\Helper;

use Cake\View\Helper;

class MenuHelper extends Helper
{
    public $helpers = ['Html' => ['className' => METOOLS . '.Html']];

    //@codingStandardsIgnoreStart
    public function _invalidMethod()
    {
    }
    //@codingStandardsIgnoreEnd

    //@codingStandardsIgnoreStart
    public function __otherInvalidMethod()
    {
    }
    //@codingStandardsIgnoreEnd

    public function articles()
    {
        $links = [
            ['First link', '/'],
            ['Second link', '/'],
        ];

        return [$links, 'First menu', ['icon' => 'home']];
    }

    public function other_items()
    {
        $links = [
            ['Third link', '/'],
            ['Fourth link', '/'],
        ];

        return [$links, 'Second menu', ['icon' => 'flag']];
    }
}
