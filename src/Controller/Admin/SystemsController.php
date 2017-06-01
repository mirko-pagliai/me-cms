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
namespace MeCms\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\I18n;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Core\Plugin;
use MeTools\Utility\Apache;

/**
 * Systems controller
 */
class SystemsController extends AppController
{
    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction('browser')) {
            $this->loadComponent(ME_CMS . '.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can clear all temporary files or logs
        if ($this->request->isAction('tmpCleaner') && in_array($this->request->getParam('pass.0'), ['all', 'logs'])) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Media browser with KCFinder.
     *
     * The KCFinder component is loaded by the `initialize()` method.
     * @return void
     * @uses MeCms\Controller\Component\KcFinderComponent::getTypes()
     */
    public function browser()
    {
        //Gets the type from the query and the supported types from configuration
        $type = $this->request->getQuery('type');
        $types = $this->KcFinder->getTypes();

        //If there's only one type, it automatically sets the query value
        if (!$type && count($types) < 2) {
            $type = collection(array_keys($types))->first();
            $this->request = $this->request->withQueryParams(compact('type'));
        }

        //Checks the type, then sets the KCFinder path
        if ($type && array_key_exists($type, $types)) {
            //Sets locale
            $locale = substr(I18n::locale(), 0, 2);
            $locale = empty($locale) ? 'en' : $locale;

            $this->set('kcfinder', sprintf('%s/kcfinder/browse.php?lang=%s&type=%s', Router::url('/vendor', true), $locale, $type));
        }

        $this->set('types', array_combine(array_keys($types), array_keys($types)));
    }

    /**
     * Changelogs viewer
     * @return void
     * @uses MeCms\Core\Plugin:all()
     * @uses MeCms\Core\Plugin:path()
     */
    public function changelogs()
    {
        foreach (Plugin::all() as $plugin) {
            $file = Plugin::path($plugin, 'CHANGELOG.md', true);

            if ($file) {
                $files[$plugin] = rtr($file);
            }
        }

        $changelog = null;

        //If a changelog file has been specified
        if ($this->request->getQuery('file')) {
            $changelog = file_get_contents(ROOT . DS . $files[$this->request->getQuery('file')]);
        }

        $this->set(compact('changelog', 'files'));
    }

    /**
     * System checkup
     * @return void
     * @uses MeCms\Core\Plugin::all()
     * @uses MeCms\Core\Plugin::path()
     * @uses MeTools\Utility\Apache::module()
     * @uses MeTools\Utility\Apache::version()
     */
    public function checkup()
    {
        $checkup['apache'] = [
            'expires' => Apache::module('mod_expires'),
            'rewrite' => Apache::module('mod_rewrite'),
            'version' => Apache::version(),
        ];

        $checkup['backups'] = [
            'path' => rtr(Configure::read(MYSQL_BACKUP . '.target') . DS),
            'writeable' => folderIsWriteable(Configure::read(MYSQL_BACKUP . '.target')),
        ];

        $checkup['cache'] = Cache::enabled();

        //Checks for PHP's extensions
        foreach (['exif', 'imagick', 'mcrypt', 'zip'] as $extension) {
            $checkup['phpExtensions'][$extension] = extension_loaded($extension);
        }

        $checkup['plugins'] = [
            'cakephp' => Configure::version(),
            'mecms' => trim(file_get_contents(Plugin::path(ME_CMS, 'version'))),
        ];

        //Gets plugins versions
        foreach (Plugin::all(['exclude' => ME_CMS]) as $plugin) {
            $file = Plugin::path($plugin, 'version', true);

            if ($file) {
                $checkup['plugins']['plugins'][$plugin] = trim(file_get_contents($file));
            } else {
                $checkup['plugins']['plugins'][$plugin] = __d('me_cms', 'n.a.');
            }
        }

        //Checks for temporary directories
        foreach ([
            LOGS,
            TMP,
            Configure::read(ASSETS . '.target'),
            CACHE,
            LOGIN_RECORDS,
            Configure::read('Thumbs.target'),
        ] as $path) {
            $checkup['temporary'][] = [
                'path' => rtr($path),
                'writeable' => folderIsWriteable($path),
            ];
        }

        //Checks for webroot directories
        foreach ([
            BANNERS,
            PHOTOS,
            WWW_ROOT . 'files',
            WWW_ROOT . 'fonts',
        ] as $path) {
            $checkup['webroot'][] = [
                'path' => rtr($path),
                'writeable' => folderIsWriteable($path),
            ];
        }

        foreach ($checkup as $key => $value) {
            $this->set($key, $value);
        };
    }

    /**
     * Internal function to clear the cache
     * @return bool
     */
    protected function clearCache()
    {
        return !array_search(false, Cache::clearAll(), true);
    }

    /**
     * Internal function to clear the sitemap
     * @return bool
     */
    protected function clearSitemap()
    {
        if (!is_readable(SITEMAP)) {
            return true;
        }

        return (new File(SITEMAP))->delete();
    }

    /**
     * Temporary cleaner (assets, cache, logs, sitemap and thumbnails)
     * @param string $type Type
     * @return \Cake\Network\Response|null|void
     * @throws MethodNotAllowedException
     * @uses clearCache()
     * @uses clearSitemap()
     */
    public function tmpCleaner($type)
    {
        if (!$this->request->is(['post', 'delete'])) {
            throw new MethodNotAllowedException();
        }

        $success = false;

        switch ($type) {
            case 'all':
                $success = clearDir(Configure::read(ASSETS . '.target')) && clearDir(LOGS)
                    && self::clearCache() && self::clearSitemap()
                    && clearDir(Configure::read('Thumbs.target'));
                break;
            case 'cache':
                $success = self::clearCache();
                break;
            case 'assets':
                $success = clearDir(Configure::read(ASSETS . '.target'));
                break;
            case 'logs':
                $success = clearDir(LOGS);
                break;
            case 'sitemap':
                $success = self::clearSitemap();
                break;
            case 'thumbs':
                $success = clearDir(Configure::read('Thumbs.target'));
                break;
        }

        if ($success) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect($this->referer(['action' => 'tmpViewer']));
    }

    /**
     * Temporary files viewer (assets, cache, logs, sitemap and thumbnails)
     * @return void
     */
    public function tmpViewer()
    {
        $assetsSize = (new Folder(Configure::read(ASSETS . '.target')))->dirsize();
        $cacheSize = (new Folder(CACHE))->dirsize();
        $logsSize = (new Folder(LOGS))->dirsize();
        $sitemapSize = is_readable(SITEMAP) ? filesize(SITEMAP) : 0;
        $thumbsSize = (new Folder(Configure::read('Thumbs.target')))->dirsize();

        $this->set([
            'cacheStatus' => Cache::enabled(),
            'totalSize' => $assetsSize + $cacheSize + $logsSize + $sitemapSize + $thumbsSize,
        ]);
        $this->set(compact('assetsSize', 'cacheSize', 'logsSize', 'sitemapSize', 'thumbsSize'));
    }
}
