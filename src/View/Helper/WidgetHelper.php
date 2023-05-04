<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace MeCms\View\Helper;

use Cake\View\Cell;
use Cake\View\Helper;

/**
 * Widget Helper
 *
 * It contains methods to render widgets.
 */
class WidgetHelper extends Helper
{
    /**
     * Internal method to get all widgets
     * @return array
     */
    protected function getAll(): array
    {
        $widgets = getConfig('Widgets.general', []);
        $widgetsHomepage = getConfig('Widgets.homepage');
        if ($this->getView()->getRequest()->is('url', ['_name' => 'homepage']) && $widgetsHomepage) {
            $widgets = $widgetsHomepage;
        }

        return $widgets ? collection($widgets)->map(function ($args, $name): array {
            if (is_array($args) && !is_string($name)) {
                [$name, $args] = [array_key_first($args), array_value_first($args)];
            }

            if (is_int($name) && is_string($args)) {
                return [$args => []];
            }

            /**
             * @var string $name
             * @var array $args
             */
            return [$name => $args];
        })->toList() : [];
    }

    /**
     * Renders all widgets
     * @return string Html code
     */
    public function all(): string
    {
        $widgets = [];

        foreach ($this->getAll() as $widget) {
            foreach ($widget as $name => $args) {
                $widgets[] = $this->widget($name, $args);
            }
        }

        return $widgets ? trim(implode(PHP_EOL, $widgets)) : '';
    }

    /**
     * Returns a widget
     * @param string $name Cell name
     * @param array $data Additional arguments for cell method
     * @param array $options Options for Cell's constructor
     * @return \Cake\View\Cell The cell instance
     */
    public function widget(string $name, array $data = [], array $options = []): Cell
    {
        $parts = explode('::', $name);
        $name = $parts[0] . 'Widgets';
        $name .= empty($parts[1]) ? '' : '::' . $parts[1];
        ksort($data, SORT_STRING);

        return $this->getView()->cell($name, $data, $options);
    }
}
