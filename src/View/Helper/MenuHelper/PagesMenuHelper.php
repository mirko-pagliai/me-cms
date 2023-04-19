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
 * PagesMenuHelper
 */
class PagesMenuHelper extends AbstractMenuHelper
{
    /**
     * Gets the links for this menu. Each links is an array of parameters
     * @return array[]
     * @throws \ErrorException
     */
    public function getLinks(): array
    {
        $params = ['plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];

        $links[] = [__d('me_cms', 'List pages'), ['controller' => 'Pages', 'action' => 'index'] + $params];

        if ($this->Identity->isGroup('admin', 'manager')) {
            $links[] = [__d('me_cms', 'Add page'), ['controller' => 'Pages', 'action' => 'add'] + $params];
        }

        $links[] = [__d('me_cms', 'List static pages'), ['controller' => 'Pages', 'action' => 'indexStatics'] + $params];

        if ($this->Identity->isGroup('admin', 'manager')) {
            $links[] = [__d('me_cms', 'List categories'), ['controller' => 'PagesCategories', 'action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add category'), ['controller' => 'PagesCategories', 'action' => 'add'] + $params];
        }

        return $links;
    }

    /**
     * Gets the options for this menu
     * @return array
     */
    public function getOptions(): array
    {
        return ['icon' => 'far copy'];
    }

    /**
     * Gets the title for this menu
     * @return string
     */
    public function getTitle(): string
    {
        return I18N_PAGES;
    }
}
