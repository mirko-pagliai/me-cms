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
 * @since       2.24.0
 */
namespace MeCms;

use Assets\Plugin as Assets;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use DatabaseBackup\Plugin as DatabaseBackup;
use DebugKit\Plugin as DebugKit;
use MeTools\Plugin as MeTools;
use RecaptchaMailhide\Plugin as RecaptchaMailhide;
use Thumber\Plugin as Thumber;

/**
 * Plugin class
 */
class Plugin extends BasePlugin
{
    /**
     * Load all the application configuration and bootstrap logic
     * @param PluginApplicationInterface $app
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app)
    {
        $pluginsToLoad = [
            Assets::class,
            DatabaseBackup::class,
            MeTools::class,
            'Recaptcha' => ['path' => ROOT . DS . 'vendor' . DS . 'crabstudio' . DS . 'recaptcha' . DS],
            RecaptchaMailhide::class,
            Thumber::class,
        ];

        foreach ($pluginsToLoad as $plugin => $config) {
            if (is_int($plugin) && !is_array($config)) {
                $plugin = $config;
                $config = [];
            }

            if (class_exists($plugin)) {
                $plugin = new $plugin;
                $plugin->bootstrap($app);
            }

            $app->addPlugin($plugin, $config);
        }

        parent::bootstrap($app);

        if (PHP_SAPI !== 'cli') {
            //Loads DebugKit, if debugging is enabled
            if (getConfig('debug') && !$app->getPlugins()->has('DebugKit')) {
                $app->addPlugin(DebugKit::class);
            }

            $app->addPlugin('Gourmet/CommonMark');
            $app->addPlugin('WyriHaximus/MinifyHtml');
        }
    }
}
