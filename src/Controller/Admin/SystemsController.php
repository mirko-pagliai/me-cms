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
 */
namespace MeCms\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use League\CommonMark\CommonMarkConverter;
use MeCms\Controller\Admin\AppController;
use MeCms\Core\Plugin;
use MeCms\Utility\Checkup;
use Symfony\Component\Finder\Finder;
use Thumber\Utility\ThumbManager;

/**
 * Systems controller
 */
class SystemsController extends AppController
{
    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->getRequest()->isAction('browser')) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins can clear all temporary files or logs
        if ($this->getRequest()->isAction('tmpCleaner') && in_array($this->getRequest()->getParam('pass.0'), ['all', 'logs'])) {
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
     * @uses \MeCms\Controller\Component\KcFinderComponent::getTypes()
     */
    public function browser(): void
    {
        //Gets the type from the query and the supported types from configuration
        $type = $this->getRequest()->getQuery('type');
        $types = $this->KcFinder->getTypes();

        //If there's only one type, it automatically sets the query value
        if (!$type && count($types) < 2) {
            $type = array_key_first($types);
            $this->setRequest($this->getRequest()->withQueryParams(compact('type')));
        }

        //Checks the type, then sets the KCFinder path
        if ($type && array_key_exists($type, $types)) {
            //Sets locale
            $this->set('kcfinder', sprintf(
                '%s/kcfinder/browse.php?lang=%s&type=%s',
                Router::url('/vendor', true),
                substr(I18n::getLocale(), 0, 2) ?: 'en',
                $type
            ));
        }

        $this->set('types', array_combine(array_keys($types), array_keys($types)));
    }

    /**
     * Changelogs viewer
     * @return void
     */
    public function changelogs(): void
    {
        foreach (Plugin::all() as $plugin) {
            $file = Plugin::path($plugin, 'CHANGELOG.md', false);

            if (is_readable($file)) {
                $files[strtolower($plugin)] = rtr($file);
            }
        }

        //If a changelog file has been specified
        if ($this->getRequest()->getQuery('file')) {
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $changelog = file_get_contents(ROOT . $files[$this->getRequest()->getQuery('file')]);
            $changelog = $converter->convertToHtml($changelog);

            $this->set(compact('changelog'));
        }

        $this->set(compact('files'));
    }

    /**
     * System checkup
     * @return void
     * @uses \MeCms\Utility\Checkup
     */
    public function checkup(): void
    {
        $Checkup = new Checkup();

        foreach (['Apache', 'KCFinder'] as $class) {
            foreach (get_class_methods($Checkup->{$class}) as $method) {
                $results[strtolower($class)][$method] = call_user_func([$Checkup->{$class}, $method]);
            }
        }

        $results += [
            'backups' => $Checkup->Backups->isWriteable(),
            'cache' => Cache::enabled(),
            'cakephp' => Configure::version(),
            'phpExtensions' => $Checkup->PHP->extensions(),
            'plugins' => $Checkup->Plugin->versions(),
            'temporary' => $Checkup->TMP->isWriteable(),
            'webroot' => $Checkup->Webroot->isWriteable(),
        ];

        array_map([$this, 'set'], array_keys($results), $results);
    }

    /**
     * Internal function to clear the cache
     * @return bool
     */
    protected function clearCache(): bool
    {
        return !array_search(false, Cache::clearAll(), true);
    }

    /**
     * Internal function to clear the sitemap
     * @return bool
     */
    protected function clearSitemap(): bool
    {
        return is_readable(SITEMAP) ? @unlink(SITEMAP) : true;
    }

    /**
     * Temporary cleaner (assets, cache, logs, sitemap and thumbnails)
     * @param string $type Type
     * @return \Cake\Http\Response|null
     * @uses clearCache()
     * @uses clearSitemap()
     */
    public function tmpCleaner(string $type): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $assetsTarget = getConfigOrFail('Assets.target');
        $success = true;
        switch ($type) {
            case 'all':
                @unlink_recursive($assetsTarget, 'empty');
                @unlink_recursive(LOGS, 'empty');
                $success = self::clearCache() && self::clearSitemap() && (new ThumbManager())->clearAll();
                break;
            case 'cache':
                $success = self::clearCache();
                break;
            case 'assets':
                @unlink_recursive($assetsTarget, 'empty');
                break;
            case 'logs':
                @unlink_recursive(LOGS, 'empty');
                break;
            case 'sitemap':
                $success = self::clearSitemap();
                break;
            case 'thumbs':
                $success = (new ThumbManager())->clearAll();
                break;
            default:
                $success = false;
        }

        [$method, $message] = $success ? ['success', I18N_OPERATION_OK] : ['error', I18N_OPERATION_NOT_OK];
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect($this->referer(['action' => 'tmpViewer']));
    }

    /**
     * Temporary files viewer (assets, cache, logs, sitemap and thumbnails)
     * @return void
     */
    public function tmpViewer(): void
    {
        $getDirSize = function ($path) {
            $size = 0;
            foreach ((new Finder())->in($path) as $file) {
                $size += $file->getSize();
            }

            return $size;
        };

        $assetsSize = $getDirSize(getConfigOrFail('Assets.target'));
        $cacheSize = $getDirSize(CACHE);
        $logsSize = $getDirSize(LOGS);
        $sitemapSize = is_readable(SITEMAP) ? filesize(SITEMAP) : 0;
        $thumbsSize = $getDirSize(getConfigOrFail('Thumber.target'));

        $this->set('cacheStatus', Cache::enabled());
        $this->set('totalSize', $assetsSize + $cacheSize + $logsSize + $sitemapSize + $thumbsSize);
        $this->set(compact('assetsSize', 'cacheSize', 'logsSize', 'sitemapSize', 'thumbsSize'));
    }
}
