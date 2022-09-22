<?php
declare(strict_types=1);

namespace TestPlugin\View\Helper;

use MeCms\View\Helper\TopbarHelper as BaseTopbarHelper;

class TopbarHelper extends BaseTopbarHelper
{
    public function build(): array
    {
        return [$this->Html->link('A link from plugin', 'http://localhost')];
    }
}
