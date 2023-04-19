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
 * @since       2.32.0
 */

namespace MeCms\View\Helper\MenuHelper;

use MeCms\View\Helper\AbstractMenuHelper;

/**
 * SystemsMenuHelper
 */
class SystemsMenuHelper extends AbstractMenuHelper
{
    /**
     * Gets the links for this menu. Each links is an array of parameters
     * @return array[]
     * @throws \ErrorException
     */
    public function getLinks(): array
    {
        //Only admins and managers can access this controller
        if (!$this->Identity->isGroup('admin', 'manager')) {
            return [];
        }

        $params = ['plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];

        //Only admins can clear temporary files
        if ($this->Identity->isGroup('admin')) {
            $links[] = [__d('me_cms', 'Temporary files'), ['controller' => 'Systems', 'action' => 'tmpViewer'] + $params];
        }

        $links[] = [__d('me_cms', 'Media browser'), ['controller' => 'Systems', 'action' => 'browser'] + $params];
        $links[] = [__d('me_cms', 'Changelogs'), ['controller' => 'Systems', 'action' => 'changelogs'] + $params];

        return $links;
    }

    /**
     * Gets the options for this menu
     * @return array
     */
    public function getOptions(): array
    {
        return ['icon' => 'wrench'];
    }

    /**
     * Gets the title for this menu
     * @return string
     */
    public function getTitle(): string
    {
        return __d('me_cms', 'System');
    }
}
