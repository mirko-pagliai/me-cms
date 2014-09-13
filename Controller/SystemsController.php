<?php
/**
 * SystemsController
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');
App::uses('System', 'MeTools.Utility');

/**
 * Systems Controller
 */
class SystemsController extends MeCmsAppController {
	/**
	 * Gets the MeCMS version number
	 * @return string MeCMS version number
	 */
	private function _getVersion() {
		return file_get_contents(CakePlugin::path('MeCms').'version');
	}
	
	/**
	 * @uses System::checkCacheStatus()
	 * @uses System::getCacheSize()
	 * @uses System::getThumbsSize()
	 */
	public function admin_cache() {
        $this->set(array(
           'cacheStatus'    => System::checkCacheStatus(),
           'cacheSize'      => System::getCacheSize(),
           'thumbsSize'     => System::getThumbsSize()
        ));
    }
	
	/**
	 * System checkup.
	 * @uses _getVersion()
	 * @uses System::checkApacheModule()
	 * @uses System::checkCache()
	 * @uses System::checkCacheStatus()
	 * @uses System::checkLogs()
	 * @uses System::checkPhpExtension()
	 * @uses System::checkPhpVersion()
	 * @uses System::checkThumbs()
	 * @uses System::checkTmp()
	 * @uses System::dirIsWritable()
	 * @uses System::getCakeVersion()
	 * @uses System::getPluginsVersion()
	 * @uses System::which()
	 */
	public function admin_checkup() {
		$phpRequired = '5.2.8';
		
		$this->set(array(
			'cache'				=> System::checkCache(),
			'cacheStatus'		=> System::checkCacheStatus(),
			'cakeVersion'		=> System::getCakeVersion(),
			'expires'			=> System::checkApacheModule('mod_expires'),
			'ffmpegthumbnailer'	=> System::which('ffmpegthumbnailer'),
			'imagick'			=> System::checkPhpExtension('imagick'),
			'logs'				=> System::checkLogs(),
			'photosWWW'			=> System::dirIsWritable(WWW_ROOT.'img'.DS.'photos'),
			'photosTmp'			=> System::dirIsWritable(TMP.'photos'),
			'phpRequired'		=> $phpRequired,
			'phpVersion'		=> System::checkPhpVersion($phpRequired),
			'plugins'			=> System::getPluginsVersion('MeCms'),
			'rewrite'			=> System::checkApacheModule('mod_rewrite'),
			'tmp'				=> System::checkTmp(),
			'thumbs'			=> System::checkThumbs(),
			'version'			=> self::_getVersion()
		));
	}
	
	/**
	 * Clear the cache.
	 */
	public function admin_clear_cache() {
		$this->request->onlyAllow('post', 'delete'); 
		
		if(System::clearCache())
			$this->Session->flash(__('The cache has been cleared'), 'success');
		else
			$this->Session->flash(__('The cache has not been cleared'), 'error');
		
		$this->redirect(array('action' => 'cache'));
		
	}
	
	/**
	 * Clear the thumbs.
	 */
	public function admin_clear_thumbs() {
		$this->request->onlyAllow('post', 'delete');
		
		if(System::clearThumbs())
			$this->Session->flash(__('Thumbnails have been deleted'), 'success');
		else
			$this->Session->flash(__('Thumbnails have not been deleted'), 'error');
		
		$this->redirect(array('action' => 'cache'));
	}
}