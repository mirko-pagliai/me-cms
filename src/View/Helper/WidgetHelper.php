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

        $widgetsCopy = [];

        //For widgets with no arguments, the widget name is the value of the
        //  array. For widgets with arguments, the widget name is the array key
        //  and the arguments are the array value
        foreach ($widgets as $name => $args) {
            if (is_int($name) && is_string($args)) {
                $name = $args;
                $args = [];
            }

            $widgetsCopy[$name] = $args;
        }

        return $widgetsCopy;
    }

    /**
     * Renders all widgets
     * @return string|void Html code
     * @uses _getAll()
     * @uses widget()
     */
    public function all()
    {
        foreach ($this->_getAll() as $name => $args) {
            $widgets[$name] = $this->widget($name, $args);
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
     * @uses Cake\View\CellTrait::cell()
     */
    public function widget($name, array $data = [], array $options = [])
    {
        return $this->_View->cell($name, $data, $options);
    }
}
