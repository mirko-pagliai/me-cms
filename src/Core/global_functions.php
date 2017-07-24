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
use Cake\Core\Configure;

if (!function_exists('getConfig')) {
    /**
     * Used to read information stored in Configure.
     *
     * It will first look in the MeCms configuration, then in the APP
     *  configuration.
     * @param string|null $var Variable to obtain
     * @param mixed $default Default value
     * @return mixed Value stored in configure or `null`
     * @since 2.19.0
     */
    function getConfig($var = null, $default = null)
    {
        $value = Configure::read(sprintf('%s.%s', ME_CMS, $var));

        if (!$value) {
            $value = Configure::read($var);
        }

        return $value ? $value : $default;
    }
}

if (!function_exists('getConfigOrFail')) {
    /**
     * Used to read information stored in Configure.
     *
     * It will first look in the MeCms configuration, then in the APP
     *  configuration.
     *
     * If no value is found, an exception will be thrown.
     * @param string $var Variable to obtain
     * @return mixed Value stored in configure
     * @since 2.19.3
     */
    function getConfigOrFail($var)
    {
        $value = Configure::read(sprintf('%s.%s', ME_CMS, $var));

        if (!$value) {
            $value = Configure::readOrFail($var);
        }

        return $value;
    }
}
