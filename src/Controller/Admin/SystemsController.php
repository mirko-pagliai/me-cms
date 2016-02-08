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

use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Utility\BannerFile;
use MeCms\Utility\PhotoFile;
use MeTools\Core\Plugin;
use MeTools\Log\Engine\FileLog;
use MeTools\Utility\Apache;
use MeTools\Utility\Asset;
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
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can view logs and clear logs
		if($this->request->isAction(['clear_logs', 'logs']))
			return $this->Auth->isGroup('admin');
		
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
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
			$this->Flash->error(__d('me_tools', 'File or directory `{0}` not writeable', rtr($this->KcFinder->getFilesPath())));
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
	 * @uses MeTools\Core\Plugin::version()
	 * @uses MeTools\Core\Plugin::versions()
	 * @uses MeTools\Log\Engine\FileLog::check()
	 * @uses MeTools\Utility\Apache::module()
	 * @uses MeTools\Utility\Apache::version()
	 * @uses MeTools\Utility\Asset::check()
	 * @uses MeTools\Utility\Asset::folder()
	 * @uses MeTools\Utility\Php::check()
	 * @uses MeTools\Utility\Php::extension()
	 * @uses MeTools\Utility\Php::version()
	 * @uses MeTools\Utility\System::cacheStatus()
	 * @uses MeTools\Utility\System::cakeVersion()
	 * @uses MeTools\Utility\System::checkCache()
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
		$phpRequired = '5.5.9';
		
		$this->set([
			'apache' => [
				'current_version'	=> Apache::version(),
				'expires'			=> Apache::module('mod_expires'),
				'rewrite'			=> Apache::module('mod_rewrite'),
			],
			'cache' => [
				'status' => System::cacheStatus()
			],
			'executables' => [
				'clean-css'			=> Unix::which('cleancss'),
				'ffmpegthumbnailer'	=> Unix::which('ffmpegthumbnailer'),
				'UglifyJS 2'		=> Unix::which('uglifyjs')
			],
			'php' => [
				'current_version'	=> Php::version(),
				'check_version'		=> Php::check($phpRequired),
				'exif'				=> Php::extension('exif'),
				'imagick'			=> Php::extension('imagick'),
				'mbstring'			=> Php::extension('mbstring'),
				'mcrypt'			=> Php::extension('mcrypt'),
				'required_version'	=> $phpRequired,
				'zip'				=> Php::extension('zip')
			],
			'plugins' => [
				'cakephp_version'	=> System::cakeVersion(),
				'plugins_version'	=> Plugin::versions('MeCms'),
				'mecms_version'		=> Plugin::version('MeCms')
			],
			'temporary' => [
				['path' => rtr(LOGS),				'writeable' => FileLog::check()],
				['path' => rtr(TMP),				'writeable' => System::checkTmp()],
				['path' => rtr(CACHE),				'writeable' => System::checkCache()],
				['path' => rtr(Thumbs::photo()),	'writeable' => Thumbs::checkPhotos()],
				['path' => rtr(Thumbs::remote()),	'writeable' => Thumbs::checkRemotes()],
				['path' => rtr(Thumbs::video()),	'writeable' => Thumbs::checkVideos()]
			],
			'webroot' => [
				['path' => rtr(Asset::folder()),			'writeable' => Asset::check()],
				['path' => rtr(WWW_ROOT.'files'),		'writeable' => folder_is_writable(WWW_ROOT.'files')],
				['path' => rtr(WWW_ROOT.'fonts'),		'writeable' => folder_is_writable(WWW_ROOT.'fonts')],
				['path' => rtr(BannerFile::folder()),	'writeable' => BannerFile::check()],
				['path' => rtr(PhotoFile::folder()),		'writeable' => PhotoFile::check()]
			]
		]);
	}
	
	/**
	 * Clears asset files
	 * @uses MeTools\Utility\Asset::clear()
	 */
	public function clear_assets() {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'cache']);
		
		if(Asset::clear())
			$this->Flash->success(__d('me_cms', 'Assets have been cleared'));
		else
			$this->Flash->error(__d('me_cms', 'Assets have not been cleared'));
		
		return $this->redirect(['action' => 'temporary']);
	}	
		
	/**
	 * Clears the cache
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
	 * @uses MeTools\Log\Engine\FileLog::clear()
	 */
	public function clear_logs() {
		if(!$this->request->is(['post', 'delete']))
			return $this->redirect(['action' => 'cache']);
		
		if(FileLog::clear())
			$this->Flash->success(__d('me_cms', 'The logs have been cleared'));
		else
			$this->Flash->error(__d('me_cms', 'The logs have not been deleted'));
		
		return $this->redirect(['action' => 'temporary']);
	}
	
	/**
	 * Clears the thumbnails
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
	 * @uses MeTools\Log\Engine\FileLog::all()
	 * @uses MeTools\Log\Engine\FileLog::parse()
	 */
	public function logs_viewer() {
		//Gets log files
		$files = FileLog::all();
		
		//If there's only one log file, it automatically sets the query value
		if(!$this->request->query('file') && count($files) < 2)
			$this->request->query['file'] = fk($files);
		
		//If a log file has been specified
		if($this->request->query('file') && $this->request->is('get'))
			$this->set('logs', array_reverse(FileLog::parse(sprintf('%s.log', $this->request->query('file')))));
		
		$this->set(compact('files'));
	}
	
	/**
	 * Manages cache, logs and thumbnails
	 * @uses MeTools\Log\Engine\FileLog::size()
	 * @uses MeTools\Utility\Asset::size()
	 * @uses MeTools\Utility\System::cacheSize()
	 * @uses MeTools\Utility\System::cacheStatus()
	 * @uses MeTools\Utility\Thumbs::size()
	 */
	public function temporary() {
        $this->set([
			'cache_size'	=> System::cacheSize(),
			'cache_status'	=> System::cacheStatus(),
			'assets_size'	=> Asset::size(),
			'logs_size'		=> FileLog::size(),
			'thumbs_size'	=> Thumbs::size()
        ]);
	}
}