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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use Cake\Core\Configure;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Core\Plugin;
use Cake\Cache\Cache;
use MeTools\Utility\Apache;

/**
 * Systems controller
 */
class SystemsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {		
		//Only admins can clear all temporary files or logs
		if($this->request->isAction('tmp_cleaner') && in_array($this->request->param('pass.0'), ['all', 'logs'])) {
			return $this->Auth->isGroup('admin');
        }
		
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
	 * Media browser with KCFinder
	 * @throws InternalErrorException
	 * @uses MeCms\Controller\Component\KcFinderComponent::checkKcfinder()
	 * @uses MeCms\Controller\Component\KcFinderComponent::checkFiles()
	 * @uses MeCms\Controller\Component\KcFinderComponent::getFilesPath()
	 * @uses MeCms\Controller\Component\KcFinderComponent::getKcfinderPath()
	 * @uses MeCms\Controller\Component\KcFinderComponent::getTypes()
	 */
	public function browser() {
		//Loads the KcFinder component
		$this->loadComponent('MeCms.KcFinder');
		
		//Checks for KCFinder
		if(!$this->KcFinder->checkKcfinder()) {
			throw new InternalErrorException(__d('me_tools', '{0} is not available', 'KCFinder'));
        }
        
		//Checks for the files directory (`APP/webroot/files`)
		if(!$this->KcFinder->checkFiles()) {
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr($this->KcFinder->getFilesPath())));
        }
		
		//Gets the supperted types from configuration
		$types = $this->KcFinder->getTypes();
		
		//If there's only one type, it automatically sets the query value
		if(!$this->request->query('type') && count($types) < 2) {
			$this->request->query['type'] = fk($types);
        }
		
		//Gets the type from the query and the types from configuration
		$type = $this->request->query('type');
		
		$locale = substr(\Cake\I18n\I18n::locale(), 0, 2);
		
		//Checks the type, then sets the KCFinder path
		if($type && array_key_exists($type, $types)) {
			$this->set('kcfinder', sprintf('%s/kcfinder/browse.php?lang=%s&type=%s', Router::url('/vendor', TRUE), empty($locale) ? 'en' : $locale, $type));
        }
		
		$this->set('types', array_combine(array_keys($types), array_keys($types)));
	}

	/**
	 * Changelogs viewer
     * @uses MeCms\Core\Plugin:all()
     * @uses MeCms\Core\Plugin:path()
	 */
	public function changelogs() {
        foreach(Plugin::all() as $plugin) {
            $files[$plugin] = rtr(Plugin::path($plugin, 'CHANGELOG.md', TRUE));
        }
		
		//If a changelog file has been specified
		if($this->request->query('file') && $this->request->is('get')) {
			//Loads the Markdown helper
			$this->helpers[] = 'MeTools.Markdown';
            
            $path = ROOT.DS.$files[$this->request->query('file')];
			
			$this->set('changelog', file_get_contents($path));
		}
		
		$this->set(compact('files'));
	}
	
	/**
	 * System checkup
	 * @uses MeCms\Core\Plugin::all()
	 * @uses MeCms\Core\Plugin::path()
	 * @uses MeTools\Utility\Apache::module()
	 * @uses MeTools\Utility\Apache::version()
	 */
	public function checkup() {
        $checkup['apache'] = [
            'expires' => Apache::module('mod_expires'),
            'rewrite' => Apache::module('mod_rewrite'),
            'version' => Apache::version(),
        ];
        
        $checkup['backups'] = [
            'path' => rtr(BACKUPS),
            'writeable'	=> folder_is_writable(BACKUPS),
        ];
        
        $checkup['cache'] = Cache::enabled();
        
        $checkup['executables'] = [
            'clean-css' => which('cleancss'),
            'UglifyJS 2' => which('uglifyjs'),
        ];
        
        //Checks for PHP's extensions
        foreach(['exif', 'imagick', 'mcrypt', 'zip'] as $extension) {
            $checkup['php_extensions'][$extension] = extension_loaded($extension);
        }
        
        $checkup['plugins'] = [
            'cakephp' => Configure::version(),
            'mecms'	=> trim(file_get_contents(Plugin::path(MECMS, 'version'))),
        ];
        
        //Gets plugins versions
        foreach(Plugin::all(['exclude' => MECMS]) as $plugin) {
            $file = Plugin::path(MECMS, 'version', TRUE);
            
            if($file) {
                $checkup['plugins']['plugins'][$plugin] = trim(file_get_contents($file));
            }
        }
        
        //Checks for temporary directories
        foreach([CACHE, LOGS, THUMBS, TMP] as $path) {
            $checkup['temporary'][] = ['path' => rtr($path), 'writeable' => folder_is_writable($path)];
        }
        
        //Checks for webroot directories
        foreach([ASSETS, BANNERS, PHOTOS, WWW_ROOT.'files', WWW_ROOT.'fonts'] as $path) {
            $checkup['webroot'][] = ['path' => rtr($path), 'writeable' => folder_is_writable($path)];
        }
        
        array_walk($checkup, function($value, $key) {
            $this->set($key, $value);
        });
	}
    
    /**
     * Internal function to clear the cache
     * @return bool
     */
    protected function clear_cache() {
        return !array_search(FALSE, Cache::clearAll(), TRUE);
    }

    /**
     * Internal function to clear the sitemap
     * @return bool
     */
    protected function clear_sitemap() {
        if(!is_readable(SITEMAP)) {
            return TRUE;
        }
        
        return (new \Cake\Filesystem\File(SITEMAP))->delete();
    }

    /**
	 * Temporary cleaner (assets, cache, logs, sitemap and thumbnails)
	 * @param string $type Type
     * @throws MethodNotAllowedException
     * @throws InternalErrorException
     * @uses clear_cache()
     * @uses clear_sitemap()
	 */
	public function tmp_cleaner($type) {
		if(!$this->request->is(['post', 'delete'])) {
			throw new MethodNotAllowedException();
        }
		
		switch($type) {
			case 'all':
				$success = clear_dir(ASSETS) && clear_dir(LOGS) && self::clear_cache() && self::clear_sitemap() && clear_dir(THUMBS);
				break;
			case 'cache':
				$success = self::clear_cache();
				break;
			case 'assets':
				$success = clear_dir(ASSETS);
				break;
			case 'logs':
				$success = clear_dir(LOGS);
				break;
            case 'sitemap':
                $success = self::clear_sitemap();
                break;
			case 'thumbs':
				$success = clear_dir(THUMBS);
				break;
            default:
                throw new InternalErrorException(__d('me_cms', 'Unknown command type'));
		}
		
		if(!empty($success)) {
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        }
		else {
			$this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }
		
		return $this->redirect(['action' => 'tmp_viewer']);
	}
	
	/**
	 * Temporary viewer (assets, cache, logs, sitemap and thumbnails)
	 */
	public function tmp_viewer() {
        $sitemap = is_readable(SITEMAP) ? filesize(SITEMAP) : 0;
        
        $this->set([
			'cache_size' => dirsize(CACHE),
			'cache_status' => Cache::enabled(),
			'assets_size' => dirsize(ASSETS),
			'logs_size' => dirsize(LOGS),
            'sitemap_size' => $sitemap,
			'thumbs_size' => dirsize(THUMBS),
			'total_size' => dirsize(CACHE) + dirsize(ASSETS) + dirsize(LOGS) + $sitemap + dirsize(THUMBS),
        ]);
	}
}