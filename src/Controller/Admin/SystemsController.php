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
use Cake\Http\Response;
use Cake\Routing\Router;
use League\CommonMark\CommonMarkConverter;
use MeCms\Core\Plugin;
use MeCms\Model\Entity\User;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Thumber\Cake\Utility\ThumbManager;
use Tools\Exceptionist;
use Tools\Filesystem;

/**
 * Systems controller
 */
class SystemsController extends AppController
{
    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        //Only admins can clear temporary files
        if ($this->getRequest()->is('action', 'tmpCleaner')) {
            return $User->get('group')->get('name') === 'admin';
        }

        return parent::isAuthorized($User);
    }

    /**
     * Internal method to check if ElFinder exists
     * @return bool
     */
    protected function elFinderExists(): bool
    {
        return is_readable(ELFINDER . 'php' . DS . 'connector.minimal.php');
    }

    /**
     * Media explorer, with ElFinder
     * @return \Cake\Http\Response|null
     */
    public function browser(): ?Response
    {
        if (!$this->elFinderExists()) {
            $this->Flash->alert(__d('me_cms', '{0} not available', 'ElFinder'));

            return $this->redirect(['_name' => 'dashboard']);
        }

        $this->set('explorer', Router::url('/vendor/elfinder/elfinder.html', true));

        return null;
    }

    /**
     * Changelogs viewer
     * @return void
     * @throws \Tools\Exception\FileNotExistsException|\Tools\Exception\KeyNotExistsException|\ErrorException
     */
    public function changelogs(): void
    {
        $files = [];

        foreach (Plugin::all() as $plugin) {
            $file = Plugin::path($plugin, 'CHANGELOG.md');

            if (is_readable($file)) {
                $files[strtolower($plugin)] = rtr($file);
            }
        }

        //If a changelog file has been specified
        $file = $this->getRequest()->getQuery('file');
        if ($file && is_string($file) && $files) {
            Exceptionist::arrayKeyExists($file, $files);
            $filename = Filesystem::makePathAbsolute($files[$file], ROOT);
            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $changelog = $converter->convertToHtml(file_get_contents($filename) ?: '');

            $this->set(compact('changelog'));
        }

        $this->set(compact('files'));
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
        return !is_writable(SITEMAP) || unlink(SITEMAP);
    }

    /**
     * Temporary cleaner (assets, cache, logs, sitemap and thumbnails)
     * @param string $type Type
     * @return \Cake\Http\Response|null
     * @throws \Tools\Exception\MethodNotExistsException
     * @throws \Tools\Exception\NotReadableException
     */
    public function tmpCleaner(string $type): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $assetsTarget = getConfigOrFail('Assets.target');
        $exceptions = ['.gitkeep', 'empty'];
        $success = true;
        switch ($type) {
            case 'all':
                Filesystem::unlinkRecursive($assetsTarget, $exceptions);
                Filesystem::unlinkRecursive(LOGS, $exceptions);
                $success = self::clearCache() && self::clearSitemap() && (new ThumbManager())->clearAll();
                break;
            case 'cache':
                $success = self::clearCache();
                break;
            case 'assets':
                Filesystem::unlinkRecursive($assetsTarget, $exceptions);
                break;
            case 'logs':
                Filesystem::unlinkRecursive(LOGS, $exceptions);
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
        $this->Flash->$method($message);

        return $this->redirect($this->referer(['action' => 'tmpViewer']));
    }

    /**
     * Temporary file viewer (assets, cache, logs, sitemap and thumbnails)
     * @return void
     */
    public function tmpViewer(): void
    {
        $getDirSize = fn(string $path): int => (int)array_sum(array_map(fn(SplFileInfo $file): int => $file->getSize() ?: 0, iterator_to_array((new Finder())->in($path))));

        $assetsSize = $getDirSize(getConfigOrFail('Assets.target'));
        $cacheSize = $getDirSize(CACHE);
        $logsSize = $getDirSize(LOGS);
        $sitemapSize = is_readable(SITEMAP) ? filesize(SITEMAP) : 0;
        $thumbsSize = $getDirSize(THUMBER_TARGET);

        $this->set('cacheStatus', Cache::enabled());
        $this->set('totalSize', $assetsSize + $cacheSize + $logsSize + $sitemapSize + $thumbsSize);
        $this->set(compact('assetsSize', 'cacheSize', 'logsSize', 'sitemapSize', 'thumbsSize'));
    }
}
