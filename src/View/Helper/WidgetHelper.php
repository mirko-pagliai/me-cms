<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
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
    protected function _getAll()
    {
        if ($this->request->isUrl(['_name' => 'homepage']) && config('Widgets.homepage')) {
            $widgets = config('Widgets.homepage');
        } else {
            $widgets = config('Widgets.general');
        }

        if (empty($widgets) || !is_array($widgets)) {
            return [];
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
     * @uses _getAll()
     * @uses widget()
     */
    public function all()
    {
        foreach ($this->_getAll() as $widget) {
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
