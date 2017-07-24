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
        $menu = [
            $this->Html->link('First link', '/'),
            $this->Html->link('Second link', '/'),
        ];

        return [$menu, 'First menu', ['icon' => 'home']];
    }

    public function other_items()
    {
        $menu = [
            $this->Html->link('Third link', '/'),
            $this->Html->link('Fourth link', '/'),
        ];

        return [$menu, 'Second menu', ['icon' => 'flag']];
    }
}
