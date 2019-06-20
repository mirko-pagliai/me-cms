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
    public $helpers = ['MeCms.Auth'];

    /**
     * Internal function to generate the menu for "posts" actions
     * @return mixed Array with links, title and title options
     */
    public function posts()
    {
        $params = ['controller' => 'Posts', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List posts'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add post'), ['action' => 'add'] + $params];

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $params['controller'] = 'PostsCategories';
            $links[] = [__d('me_cms', 'List categories'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add category'), ['action' => 'add'] + $params];
        }

        $links[] = [__d('me_cms', 'List tags'), [
            'controller' => 'PostsTags',
            'action' => 'index',
        ] + $params];

        return [$links, I18N_POSTS, ['icon' => 'far file-alt']];
    }

    /**
     * Internal function to generate the menu for "pages" actions
     * @return mixed Array with links, title and title options
     */
    public function pages()
    {
        $params = ['controller' => 'Pages', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List pages'), ['action' => 'index'] + $params];

        //Only admins and managers can access these actions
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            $links[] = [__d('me_cms', 'Add page'), ['action' => 'add'] + $params];

            $params['controller'] = 'PagesCategories';
            $links[] = [__d('me_cms', 'List categories'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add category'), ['action' => 'add'] + $params];
        }

        $links[] = [__d('me_cms', 'List static pages'), [
            'controller' => 'Pages',
            'action' => 'indexStatics',
        ] + $params];

        return [$links, I18N_PAGES, ['icon' => 'far copy']];
    }

    /**
     * Internal function to generate the menu for "photos" actions
     * @return mixed Array with links, title and title options
     */
    public function photos()
    {
        $params = ['controller' => 'Photos', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List photos'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Upload photos'), ['action' => 'upload'] + $params];

        $params['controller'] = 'PhotosAlbums';
        $links[] = [__d('me_cms', 'List albums'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add album'), ['action' => 'add'] + $params];

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

        $params = ['controller' => 'Banners', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List banners'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Upload banners'), ['action' => 'upload'] + $params];

        //Only admin can access this controller
        if ($this->Auth->isGroup('admin')) {
            $params['controller'] = 'BannersPositions';
            $links[] = [__d('me_cms', 'List positions'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add position'), ['action' => 'add'] + $params];
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

        $params = ['controller' => 'Users', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List users'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add user'), ['action' => 'add'] + $params];

        //Only admins can access these actions
        if ($this->Auth->isGroup('admin')) {
            $params['controller'] = 'UsersGroups';
            $links[] = [__d('me_cms', 'List groups'), ['action' => 'index'] + $params];
            $links[] = [__d('me_cms', 'Add group'), ['action' => 'add'] + $params];
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

        $params = ['controller' => 'Backups', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'List backups'), ['action' => 'index'] + $params];
        $links[] = [__d('me_cms', 'Add backup'), ['action' => 'add'] + $params];

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

        $params = ['controller' => 'Systems', 'plugin' => 'MeCms', 'prefix' => ADMIN_PREFIX];
        $links[] = [__d('me_cms', 'Temporary files'), ['action' => 'tmpViewer'] + $params];
        $links[] = [__d('me_cms', 'System checkup'), ['action' => 'checkup'] + $params];
        $links[] = [__d('me_cms', 'Media browser'), ['action' => 'browser'] + $params];
        $links[] = [__d('me_cms', 'Changelogs'), ['action' => 'changelogs'] + $params];

        //Only admins can manage logs
        if ($this->Auth->isGroup('admin')) {
            $links[] = [__d('me_cms', 'Log management'), [
                'controller' => 'Logs',
                'action' => 'index',
            ] + $params];
        }

        return [$links, __d('me_cms', 'System'), ['icon' => 'wrench']];
    }
}
