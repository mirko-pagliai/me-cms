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

use DatabaseBackup\Utility\BackupManager;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\Network\Exception\InternalErrorException;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Utility\BannerFile;
use MeCms\Utility\PhotoFile;
use MeTools\Cache\Cache;
use MeTools\Core\Plugin;
use MeTools\Utility\Apache;
use MeTools\Utility\Php;

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
		if($this->request->isAction('tmp_cleaner') && in_array($this->request->param('pass.0'), ['all', 'logs']))
			return $this->Auth->isGroup('admin');
		
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
		if(!$this->KcFinder->checkKcfinder())
			throw new InternalErrorException(__d('me_cms', '{0} is not present into {1}', 'KCFinder', rtr($this->KcFinder->getKcfinderPath())));
		
		//Checks for the files directory (`APP/webroot/files`)
		if(!$this->KcFinder->checkFiles())
			throw new InternalErrorException(__d('me_tools', 'File or directory `{0}` not writeable', rtr($this->KcFinder->getFilesPath())));
		
		//Gets the supperted types from configuration
		$types = $this->KcFinder->getTypes();
		
		//If there's only one type, it automatically sets the query value
		if(!$this->request->query('type') && count($types) < 2)
			$this->request->query['type'] = fk($types);
		
		//Gets the type from the query and the types from configuration
		$type = $this->request->query('type');
		
		$locale = substr(\Cake\I18n\I18n::locale(), 0, 2);
		
		//Checks the type, then sets the KCFinder path
		if($type && array_key_exists($type, $types))
			$this->set('kcfinder', sprintf('%s/kcfinder/browse.php?lang=%s&type=%s', Router::url('/vendor', TRUE), empty($locale) ? 'en' : $locale, $type));
		
		$this->set('types', array_combine(array_keys($types), array_keys($types)));
	}

	/**
	 * Changelogs viewer
	 */
	public function changelogs() {
		//Gets all changelog files. 
		//Searchs into `ROOT` and all loaded plugins.
		foreach(am([ROOT.DS], Plugin::path()) as $path) {
			//For each changelog file in the current path
			foreach((new Folder($path))->find('CHANGELOG(\..+)?') as $file)
				$files[] = rtr($path.$file);
		}
		
		//If a changelog file has been specified
		if($this->request->query('file') && $this->request->is('get')) {
			//Loads the Markdown helper
			$this->helpers[] = 'MeTools.Markdown';
			
			$this->set('changelog', @file_get_contents(ROOT.DS.$files[$this->request->query('file')]));
		}
		
		$this->set(compact('files'));
	}
	
	/**
	 * System checkup
	 * @uses MeCms\Utility\BannerFile::check()
	 * @uses MeCms\Utility\BannerFile::folder()
	 * @uses MeCms\Utility\PhotoFile::check()
	 * @uses MeCms\Utility\PhotoFile::folder()
	 * @uses MeTools\Core\Plugin::version()
	 * @uses MeTools\Core\Plugin::versions()
	 * @uses MeTools\Utility\Apache::module()
	 * @uses MeTools\Utility\Apache::version()
	 * @uses MeTools\Utility\Php::check()
	 * @uses MeTools\Utility\Php::extension()
	 * @uses MeTools\Utility\Php::version()
	 */
	public function checkup() {
		$phpRequired = '5.5.9';
		
		$this->set([
			'apache' => [
				'expires'	=> Apache::module('mod_expires'),
				'rewrite'	=> Apache::module('mod_rewrite'),
				'version'	=> Apache::version(),
			],
			'backups' => [
				'path'		=> rtr(BackupManager::path()),
				'writeable'	=> folder_is_writable(BackupManager::path())
			],
			'cache' => [
				'status' => Cache::enabled()
			],
			'executables' => [
				'clean-css'		=> which('cleancss'),
				'UglifyJS 2'	=> which('uglifyjs')
			],
			'php' => [
				'check'		=> Php::check($phpRequired),
				'exif'		=> Php::extension('exif'),
				'imagick'	=> Php::extension('imagick'),
				'mbstring'	=> Php::extension('mbstring'),
				'mcrypt'	=> Php::extension('mcrypt'),
				'required'	=> $phpRequired,
				'version'	=> Php::version(),
				'zip'		=> Php::extension('zip')
			],
			'plugins' => [
				'cakephp_version'	=> Configure::version(),
				'plugins_version'	=> Plugin::versions('MeCms'),
				'mecms_version'		=> Plugin::version('MeCms')
			],
			'temporary' => [
				['path' => rtr(LOGS),	'writeable' => folder_is_writable(LOGS)],
				['path' => rtr(TMP),	'writeable' => folder_is_writable(TMP)],
				['path' => rtr(CACHE),	'writeable' => folder_is_writable(CACHE)],
				['path' => rtr(THUMBS),	'writeable' => folder_is_writable(THUMBS)],
			],
			'webroot' => [
				['path' => rtr(ASSETS),					'writeable' => folder_is_writable(ASSETS)],
				['path' => rtr(WWW_ROOT.'files'),		'writeable' => folder_is_writable(WWW_ROOT.'files')],
				['path' => rtr(WWW_ROOT.'fonts'),		'writeable' => folder_is_writable(WWW_ROOT.'fonts')],
				['path' => rtr(BannerFile::folder()),	'writeable' => BannerFile::check()],
				['path' => rtr(PhotoFile::folder()),		'writeable' => PhotoFile::check()]
			]
		]);
	}
	
	/**
	 * Temporary cleaner (assets, cache, logs and thumbnails)
	 * @param string $type Type
	 * @uses MeTools\Cache\Cache::clearAll()
	 */
	public function tmp_cleaner($type) {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'tmp_viewer']);
		
		switch($type) {
			case 'all':
				$success = clear_dir(ASSETS) && clear_dir(LOGS) && Cache::clearAll() && clear_dir(THUMBS);
				break;
			case 'cache':
				$success = Cache::clearAll();
				break;
			case 'assets':
				$success = clear_dir(ASSETS);
				break;
			case 'logs':
				$success = clear_dir(LOGS);
				break;
			case 'thumbs':
				$success = clear_dir(THUMBS);
				break;
		}
		
		if(!empty($success))
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
		else
			$this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
		
		return $this->redirect(['action' => 'tmp_viewer']);
	}
	
	/**
	 * Temporary viewer (assets, cache, logs and thumbnails)
	 */
	public function tmp_viewer() {
        $this->set([
			'all_size'		=> dirsize(CACHE) + dirsize(ASSETS) + dirsize(LOGS) + dirsize(THUMBS),
			'cache_size'	=> dirsize(CACHE),
			'cache_status'	=> Cache::enabled(),
			'assets_size'	=> dirsize(ASSETS),
			'logs_size'		=> dirsize(LOGS),
			'thumbs_size'	=> dirsize(THUMBS)
        ]);
	}
}