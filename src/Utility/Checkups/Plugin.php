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
 * @since       2.22.8
 */
namespace MeCms\Utility\Checkups;

use MeCms\Core\Plugin as BasePlugin;
use MeCms\Utility\Checkups\AbstractCheckup;

/**
 * Checkup for plugins
 */
class Plugin extends AbstractCheckup
{
    /**
     * Returns the version number for each plugin
     * @return array
     * @uses MeCms\Core\Plugin::all()
     * @uses MeCms\Core\Plugin::path()
     */
    public function versions()
    {
        $Plugin = new BasePlugin;

        $plugins['me_cms'] = trim(file_get_contents($Plugin->path('MeCms', 'version')));

        foreach ($Plugin->all(['exclude' => 'MeCms']) as $plugin) {
            $file = $Plugin->path($plugin, 'version', false);
            $plugins['others'][$plugin] = __d('me_cms', 'n.a.');

            if (is_readable($file)) {
                $plugins['others'][$plugin] = trim(file_get_contents($file));
            }
        }

        return $plugins;
    }
}
