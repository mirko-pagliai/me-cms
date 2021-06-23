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
 * @see         http://api.cakephp.org/3.7/class-Cake.Core.Plugin.html
 */

namespace MeCms\Core;

use MeTools\Core\Plugin as BasePlugin;

/**
 * An utility to handle plugins
 */
class Plugin extends BasePlugin
{
    /**
     * Gets all loaded plugins.
     *
     * Available options are:
     *  - `core`, if `false` excludes the core plugins;
     *  - `exclude`, a plugin or array of plugins to be excluded;
     *  - `mecms_core`, if `false` excludes plugins automatically requested by MeCms;
     *  - `order`, if `true` the plugins will be sorted.
     * @param array<string, string|bool|array> $options Options
     * @return array<string> Plugins
     */
    public static function all(array $options = []): array
    {
        $options += ['exclude' => [], 'order' => true, 'mecms_core' => true];

        if (!$options['mecms_core']) {
            $options['exclude'] = array_merge((array)$options['exclude'], ['MeTools', 'Assets', 'DatabaseBackup', 'Recaptcha', 'RecaptchaMailhide', 'StopSpam', 'Thumber/Cake', 'Tokens', 'WyriHaximus/MinifyHtml']);
        }

        $plugins = parent::all($options);

        if ($options['order']) {
            $key = array_search('MeCms', $plugins);

            if ($key) {
                unset($plugins[$key]);
                array_unshift($plugins, 'MeCms');
            }
        }

        return $plugins;
    }
}
