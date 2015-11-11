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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use Cake\Core\Configure;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Utility\BannerFile;
use MeCms\Utility\PhotoFile;
use MeTools\Utility\Apache;
use MeTools\Core\Plugin;
use MeTools\Utility\Php;
use MeTools\Utility\System;
use MeTools\Utility\Thumbs;
use MeTools\Utility\Unix;

/**
 * Systems controller
 */
class SystemsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\AppController::isAuthorized()
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can view logs and clear logs
		if($this->request->isAction(['clear_logs', 'logs']))
			return $this->Auth->isGroup('admin');
		
		//Admins and managers can access other actions
		return parent::isAuthorized($user);
	}
	
	/**
	 * Media browser with KCFinder
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
			$this->Flash->error(__d('me_cms', '{0} is not present into {1}', 'KCFinder', rtr($this->KcFinder->getKcfinderPath())));
			$this->redirect(['_name' => 'dashboard']);
		}
		
		//Checks for the files directory (`APP/webroot/files`)
		if(!$this->KcFinder->checkFiles()) {
			$this->Flash->error(__d('me_cms', 'The directory {0} is not readable or writable', rtr($this->KcFinder->getFilesPath())));
			$this->redirect(['_name' => 'dashboard']);
		}
		
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
	 * @uses MeTools\Utility\System::changelogs()
	 */
	public function changelogs() {
		//Gets changelogs files
		$files = System::changelogs();
		
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
	 * @uses MeTools\Utility\Apache::module()
	 * @uses MeTools\Utility\Apache::version()
	 * @uses MeTools\Core\Plugin::version()
	 * @uses MeTools\Core\Plugin::versions()
	 * @uses MeTools\Utility\Php::checkVersion()
	 * @uses MeTools\Utility\Php::extension()
	 * @uses MeTools\Utility\Php::version()
	 * @uses MeTools\Utility\System::cacheStatus()
	 * @uses MeTools\Utility\System::cakeVersion()
	 * @uses MeTools\Utility\System::checkCache()
	 * @uses MeTools\Utility\System::checkLogs()
	 * @uses MeTools\Utility\System::checkTmp()
	 * @uses MeTools\Utility\Thumbs::checkPhotos()
	 * @uses MeTools\Utility\Thumbs::checkRemotes()
	 * @uses MeTools\Utility\Thumbs::checkVideos()
	 * @uses MeTools\Utility\Thumbs::photo()
	 * @uses MeTools\Utility\Thumbs::remote()
	 * @uses MeTools\Utility\Thumbs::video()
	 * @uses MeTools\Utility\Unix::which()
	 */
	public function checkup() {
		$phpRequired = '5.4.16';
		
		$this->set([
			'apache' => [
				'current_version'	=> Apache::version(),
				'expires'			=> Apache::module('mod_expires'),
				'rewrite'			=> Apache::module('mod_rewrite'),
			],
			'banners' => [
				'check'	=> BannerFile::check(),
				'path'	=> rtr(BannerFile::folder())
			],
			'ffmpegthumbnailer' => [
				'check' => Unix::which('ffmpegthumbnailer')
			],
			'files' => [
				'check' => folder_is_writable(WWW_ROOT.'files'),
				'path'	=> rtr(WWW_ROOT.'files')
			],
			'php' => [
				'current_version'	=> Php::version(),
				'check_version'		=> Php::checkVersion($phpRequired),
				'exif'				=> Php::extension('exif'),
				'imagick'			=> Php::extension('imagick'),
				'mbstring'			=> Php::extension('mbstring'),
				'mcrypt'			=> Php::extension('mcrypt'),
				'required_version'	=> $phpRequired,
				'zip'				=> Php::extension('zip'),
			],
			'photos' => [
				'check'	=> PhotoFile::check(),
				'path'	=> rtr(PhotoFile::folder())
			],
			'plugins' => [
				'cakephp_version'	=> System::cakeVersion(),
				'plugins_version'	=> Plugin::versions('MeCms'),
				'mecms_version'		=> Plugin::version('MeCms')
			],
			'thumbs' => [
				'photos_path'		=> rtr(Thumbs::photo()),
				'photos_writable'	=> Thumbs::checkPhotos(),
				'remotes_path'		=> rtr(Thumbs::remote()),
				'remotes_writable'	=> Thumbs::checkRemotes(),
				'videos_path'		=> rtr(Thumbs::video()),
				'videos_writable'	=> Thumbs::checkVideos()
			],
			'tmp' => [
				'cache_status'		=> System::cacheStatus(),
				'cache_writable'	=> System::checkCache(),
				'logs_path'			=> rtr(LOGS),
				'logs_writable'		=> System::checkLogs(),
				'tmp_writable'		=> System::checkTmp()
			]
		]);
	}
		
	/**
	 * Clears the cache.
	 * @uses MeTools\Utility\System::clearCache()
	 */
	public function clear_cache() {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'cache']);
		
		if(System::clearCache())
			$this->Flash->success(__d('me_cms', 'The cache has been cleared'));
		else
			$this->Flash->error(__d('me_cms', 'The cache has not been cleared'));
		
		return $this->redirect(['action' => 'temporary']);
	}
	
	/**
	 * Clears logs
	 * @uses MeTools\Utility\System::clearLogs()
	 */
	public function clear_logs() {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'cache']);
		
		if(System::clearLogs())
			$this->Flash->success(__d('me_cms', 'The logs have been cleared'));
		else
			$this->Flash->error(__d('me_cms', 'The logs have not been deleted'));
		
		return $this->redirect(['action' => 'temporary']);
	}
	
	/**
	 * Clears the thumbnails.
	 * @uses MeTools\Utility\Thumbs::clear()
	 */
	public function clear_thumbs() {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'cache']);
		
		if(Thumbs::clear())
			$this->Flash->success(__d('me_cms', 'The thumbnails have been deleted'));
		else
			$this->Flash->error(__d('me_cms', 'The thumbnails have not been deleted'));
		
		return $this->redirect(['action' => 'temporary']);
	}
	
	/**
	 * Log viewer
	 * @uses MeTools\Utility\System::logs()
	 */
	public function logs() {
		//Gets log files
		$files = System::logs();
		
		//If a log file has been specified
		if(!empty($this->request->query['file']) && $this->request->is('get'))
			$this->set('log', @file_get_contents(LOGS.$files[$this->request->query('file')]));
		
		$this->set(compact('files'));
	}
	
	/**
	 * Manages cache and thumbnails
	 * @uses MeTools\Utility\System::cacheSize()
	 * @uses MeTools\Utility\System::cacheStatus()
	 * @uses MeTools\Utility\System::logsSize()
	 * @uses MeTools\Utility\Thumbs::size()
	 */
	public function temporary() {
        $this->set([
			'cache_size'	=> System::cacheSize(),
			'cache_status'	=> System::cacheStatus(),
			'logs_size'		=> System::logsSize(),
			'thumbs_size'	=> Thumbs::size()
        ]);
	}
}