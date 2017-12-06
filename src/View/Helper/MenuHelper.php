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
 * @see         MeCms\View\Helper\MenuBuilderHelper
 */
namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * Menu Helper.
 *
 * This helper contains methods that will be called automatically to generate
 *  the menu of the admin layout.
 * You don't need to call these methods manually, use instead the `MenuBuilder`
 *  helper.
 */
class MenuHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = [ME_CMS . '.Auth'];

    /**
     * Internal function to generate the menu for "posts" actions
     * @return mixed Array with links, title and title options
     */
    public function posts()
    {
        $links[] = [__d('me_cms', 'List posts'), [
            'controller' => 'Posts',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Add post'), [
            'controller' => 'Posts',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $links[] = [__d('me_cms', 'List categories'), [
                'controller' => 'PostsCategories',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
            $links[] = [__d('me_cms', 'Add category'), [
                'controller' => 'PostsCategories',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
        }

        $links[] = [__d('me_cms', 'List tags'), [
            'controller' => 'PostsTags',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        return [$links, I18N_POSTS, ['icon' => 'file-text-o']];
    }

    /**
     * Internal function to generate the menu for "pages" actions
     * @return mixed Array with links, title and title options
     */
    public function pages()
    {
        $links[] = [__d('me_cms', 'List pages'), [
            'controller' => 'Pages',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $links[] = [__d('me_cms', 'Add page'), [
                'controller' => 'Pages',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
            $links[] = [__d('me_cms', 'List categories'), [
                'controller' => 'PagesCategories',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
            $links[] = [__d('me_cms', 'Add category'), [
                'controller' => 'PagesCategories',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
        }

        $links[] = [__d('me_cms', 'List static pages'), [
            'controller' => 'Pages',
            'action' => 'indexStatics',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        return [$links, I18N_PAGES, ['icon' => 'files-o']];
    }

    /**
     * Internal function to generate the menu for "photos" actions
     * @return mixed Array with links, title and title options
     */
    public function photos()
    {
        $links[] = [__d('me_cms', 'List photos'), [
            'controller' => 'Photos',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Upload photos'), [
            'controller' => 'Photos',
            'action' => 'upload',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'List albums'), [
            'controller' => 'PhotosAlbums',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Add album'), [
            'controller' => 'PhotosAlbums',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        return [$links, I18N_PHOTOS, ['icon' => 'camera-retro']];
    }

    /**
     * Internal function to generate the menu for "banners" actions
     * @return mixed Array with links, title and title options
     */
    public function banners()
    {
        //Only admins and managers can access these controllers
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $links[] = [__d('me_cms', 'List banners'), [
            'controller' => 'Banners',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Upload banners'), [
            'controller' => 'Banners',
            'action' => 'upload',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        //Only admin can access this controller
        if ($this->Auth->isGroup('admin')) {
            $links[] = [__d('me_cms', 'List positions'), [
                'controller' => 'BannersPositions',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
            $links[] = [__d('me_cms', 'Add position'), [
                'controller' => 'BannersPositions',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
        }

        return [$links, __d('me_cms', 'Banners'), ['icon' => 'shopping-cart']];
    }

    /**
     * Internal function to generate the menu for "users" actions
     * @return mixed Array with links, title and title options
     */
    public function users()
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $links[] = [__d('me_cms', 'List users'), [
            'controller' => 'Users',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Add user'), [
            'controller' => 'Users',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        //Only admins can access these actions
        if ($this->Auth->isGroup('admin')) {
            $links[] = [__d('me_cms', 'List groups'), [
                'controller' => 'UsersGroups',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
            $links[] = [__d('me_cms', 'Add group'), [
                'controller' => 'UsersGroups',
                'action' => 'add',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
        }

        return [$links, I18N_USERS, ['icon' => 'users']];
    }

    /**
     * Internal function to generate the menu for "backups" actions
     * @return mixed Array with links, title and title options
     */
    public function backups()
    {
        //Only admins can access this controller
        if (!$this->Auth->isGroup('admin')) {
            return;
        }

        $links[] = [__d('me_cms', 'List backups'), [
            'controller' => 'Backups',
            'action' => 'index',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Add backup'), [
            'controller' => 'Backups',
            'action' => 'add',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        return [$links, __d('me_cms', 'Backups'), ['icon' => 'database']];
    }

    /**
     * Internal function to generate the menu for "systems" actions
     * @return mixed Array with links, title and title options
     */
    public function systems()
    {
        //Only admins and managers can access this controller
        if (!$this->Auth->isGroup(['admin', 'manager'])) {
            return;
        }

        $links[] = [__d('me_cms', 'Temporary files'), [
            'controller' => 'Systems',
            'action' => 'tmpViewer',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        //Only admins can manage logs
        if ($this->Auth->isGroup('admin')) {
            $links[] = [__d('me_cms', 'Log management'), [
                'controller' => 'Logs',
                'action' => 'index',
                'plugin' => ME_CMS,
                'prefix' => ADMIN_PREFIX,
            ]];
        }

        $links[] = [__d('me_cms', 'System checkup'), [
            'controller' => 'Systems',
            'action' => 'checkup',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Media browser'), [
            'controller' => 'Systems',
            'action' => 'browser',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];
        $links[] = [__d('me_cms', 'Changelogs'), [
            'controller' => 'Systems',
            'action' => 'changelogs',
            'plugin' => ME_CMS,
            'prefix' => ADMIN_PREFIX,
        ]];

        return [$links, __d('me_cms', 'System'), ['icon' => 'wrench']];
    }
}
