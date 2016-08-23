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
namespace MeCms\Core;

use MeTools\Core\Plugin as BasePlugin;

/**
 * An utility to handle plugins.
 *
 * Rewrites {@link http://api.cakephp.org/3.3/class-Cake.Core.Plugin.html Plugin}.
 */
class Plugin extends BasePlugin
{
    /**
     * Gets all loaded plugins.
     *
     * Available options are:
     *  - `core`, if `false` exclude the core plugins;
     *  - `exclude`, a plugin as string or an array of plugins to be excluded;
     *  - `order`, if `true` the plugins will be sorted.
     * @param array $options Options
     * @return array Plugins
     * @uses MeTools\Core\Plugin::all()
     */
    public static function all(array $options = [])
    {
        $options = am([
            'core' => false,
            'except' => [],
            'order' => true,
        ], $options);
        
        $plugins = parent::all($options);
        
        if ($options['order']) {
            $key = array_search(MECMS, $plugins);
            
            if ($key) {
                unset($plugins[$key]);
                array_unshift($plugins, MECMS);
            }
        }
        
        return $plugins;
    }
}
