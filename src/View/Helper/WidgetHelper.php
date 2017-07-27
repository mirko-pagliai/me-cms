<?php
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
    protected function getAll()
    {
        $widgets = getConfig('Widgets.general', []);

        if ($this->request->isUrl(['_name' => 'homepage']) && getConfig('Widgets.homepage')) {
            $widgets = getConfig('Widgets.homepage');
        }

        return collection($widgets)->map(function ($args, $name) {
            if (is_string($name) && is_array($args)) {
                return [$name => $args];
            } elseif (is_string($args)) {
                return [$args => []];
            }

            $name = collection(array_keys($args))->first();
            $args = collection($args)->first();

            if (is_int($name) && is_string($args)) {
                return [$args => []];
            }

            return [$name => $args];
        })->toList();
    }

    /**
     * Renders all widgets
     * @return string|void Html code
     * @uses getAll()
     * @uses widget()
     */
    public function all()
    {
        foreach ($this->getAll() as $widget) {
            foreach ($widget as $name => $args) {
                $widgets[] = $this->widget($name, $args);
            }
        }

        if (empty($widgets)) {
            return;
        }

        return trim(implode(PHP_EOL, $widgets));
    }

    /**
     * Returns a widget
     * @param string $name Cell name
     * @param array $data Additional arguments for cell method
     * @param array $options Options for Cell's constructor
     * @return Cake\View\Cell The cell instance
     */
    public function widget($name, array $data = [], array $options = [])
    {
        $parts = explode('::', $name);

        $name = $parts[0] . 'Widgets';

        if (!empty($parts[1])) {
            $name = sprintf('%s::%s', $name, $parts[1]);
        }

        return $this->getView()->cell($name, $data, $options);
    }
}
