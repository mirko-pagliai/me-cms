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
 * @see         MeCms\View\Helper\MenuBuilderHelper
 */
namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * Menu Helper.
 *
 * This helper contains methods that will be called automatically to generate
 * the menu of the admin layout.
 * You do not need to call these methods manually.
 */
class MenuHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = [
        'Html' => ['className' => METOOLS . '.Html'],
        ME_CMS . '.Auth',
    ];

    /**
     * Internal function to generate the menu for "posts" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function posts()
    {
        $menu[] = $this->Html->link(__d('me_cms', 'List posts'), [
            'controller' => 'Posts',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Add post'), [
            'controller' => 'Posts',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $menu[] = $this->Html->link(__d('me_cms', 'List categories'), [
                'controller' => 'PostsCategories',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
            $menu[] = $this->Html->link(__d('me_cms', 'Add category'), [
                'controller' => 'PostsCategories',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
        }

        $menu[] = $this->Html->link(__d('me_cms', 'List tags'), [
            'controller' => 'PostsTags',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        return [$menu, __d('me_cms', 'Posts'), ['icon' => 'file-text-o']];
    }

    /**
     * Internal function to generate the menu for "pages" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function pages()
    {
        $menu[] = $this->Html->link(__d('me_cms', 'List pages'), [
            'controller' => 'Pages',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $menu[] = $this->Html->link(__d('me_cms', 'Add page'), [
                'controller' => 'Pages',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
            $menu[] = $this->Html->link(__d('me_cms', 'List categories'), [
                'controller' => 'PagesCategories',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
            $menu[] = $this->Html->link(__d('me_cms', 'Add category'), [
                'controller' => 'PagesCategories',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
        }

        $menu[] = $this->Html->link(__d('me_cms', 'List static pages'), [
            'controller' => 'Pages',
            'action' => 'indexStatics',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        return [$menu, __d('me_cms', 'Pages'), ['icon' => 'files-o']];
    }

    /**
     * Internal function to generate the menu for "photos" actions
     * @return mixed Array with menu, title and link options
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function photos()
    {
        $menu[] = $this->Html->link(__d('me_cms', 'List photos'), [
            'controller' => 'Photos',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Upload photos'), [
            'controller' => 'Photos',
            'action' => 'upload',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'List albums'), [
            'controller' => 'PhotosAlbums',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Add album'), [
            'controller' => 'PhotosAlbums',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        return [$menu, __d('me_cms', 'Photos'), ['icon' => 'camera-retro']];
    }

    /**
     * Internal function to generate the menu for "banners" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function banners()
    {
        //Only admins and managers can access these controllers
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $menu[] = $this->Html->link(__d('me_cms', 'List banners'), [
            'controller' => 'Banners',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Upload banners'), [
            'controller' => 'Banners',
            'action' => 'upload',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        //Only admin can access this controller
        if ($this->Auth->isGroup('admin')) {
            $menu[] = $this->Html->link(__d('me_cms', 'List positions'), [
                'controller' => 'BannersPositions',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
            $menu[] = $this->Html->link(__d('me_cms', 'Add position'), [
                'controller' => 'BannersPositions',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
        }

        return [$menu, __d('me_cms', 'Banners'), ['icon' => 'shopping-cart']];
    }

    /**
     * Internal function to generate the menu for "users" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function users()
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $menu[] = $this->Html->link(__d('me_cms', 'List users'), [
            'controller' => 'Users',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Add user'), [
            'controller' => 'Users',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        //Only admins can access these actions
        if ($this->Auth->isGroup('admin')) {
            $menu[] = $this->Html->link(__d('me_cms', 'List groups'), [
                'controller' => 'UsersGroups',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
            $menu[] = $this->Html->link(__d('me_cms', 'Add group'), [
                'controller' => 'UsersGroups',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
        }

        return [$menu, __d('me_cms', 'Users'), ['icon' => 'users']];
    }

    /**
     * Internal function to generate the menu for "backups" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     */
    public function backups()
    {
        //Only admins can access this controller
        if (!$this->Auth->isGroup('admin')) {
            return;
        }

        $menu[] = $this->Html->link(__d('me_cms', 'List backups'), [
            'controller' => 'Backups',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Add backup'), [
            'controller' => 'Backups',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        return [$menu, __d('me_cms', 'Backups'), ['icon' => 'database']];
    }

    /**
     * Internal function to generate the menu for "systems" actions
     * @return mixed Array with menu, title and link options
     * @uses MeCms\View\Helper\AuthHelper::isGroup()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    public function systems()
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $menu[] = $this->Html->link(__d('me_cms', 'Temporary files'), [
            'controller' => 'Systems',
            'action' => 'tmpViewer',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        //Only admins can manage logs
        if ($this->Auth->isGroup('admin')) {
            $menu[] = $this->Html->link(__d('me_cms', 'Log management'), [
                'controller' => 'Logs',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]);
        }

        $menu[] = $this->Html->link(__d('me_cms', 'System checkup'), [
            'controller' => 'Systems',
            'action' => 'checkup',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Media browser'), [
            'controller' => 'Systems',
            'action' => 'browser',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);
        $menu[] = $this->Html->link(__d('me_cms', 'Changelogs'), [
            'controller' => 'Systems',
            'action' => 'changelogs',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]);

        return [$menu, __d('me_cms', 'System'), ['icon' => 'wrench']];
    }
}
