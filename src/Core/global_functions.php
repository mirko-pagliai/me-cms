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
