<?php
declare(strict_types=1);

namespace TestPlugin\View\Helper;

use Cake\View\Helper;

class TopbarHelper extends Helper
{
    public $helpers = [
        'Html' => ['className' => 'MeTools.Html'],
    ];

    public function build(): array
    {
        return [
            $this->Html->link('A link from plugin', 'http://localhost'),
        ];
    }
}
