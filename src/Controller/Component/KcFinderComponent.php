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
namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * A component to handle KCFinder
 */
class KcFinderComponent extends Component {
	/**
	 * Components
	 * @var array
	 */
    public $components = ['MeCms.Auth'];

	/**
	 * Checks for KCFinder
	 * @return bool
	 * @uses getKcfinderPath()
	 */
	public function checkKcfinder() {
		return is_readable($this->getKcfinderPath().DS.'index.php');
	}
	
	/**
	 * Checks if the files directory is writeable
	 * @return bool
	 * @uses getFilesPath()
	 */
	public function checkFiles() {
		return folder_is_writeable($this->getFilesPath());
	}
	
	/**
	 * Sets the configuration for KCFinder.
	 * It's automatically called by `beforeRender()` when the component is loaded.
	 * @return bool
	 * @see beforeRender()
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses getFilesPath()
	 * @uses getTypes()
	 */
	public function configure() {
		if($this->request->session()->check('KCFINDER'))
			return TRUE;
		
		//Default configuration
		$default = [
			'denyExtensionRename'	=> TRUE,
			'denyUpdateCheck'		=> TRUE,
			'dirnameChangeChars'	=> [' ' => '_', ':' => '_'],
			'disabled'				=> FALSE,
			'filenameChangeChars'	=> [' ' => '_', ':' => '_'],
			'jpegQuality'			=> 100,
			'uploadDir'				=> $this->getFilesPath(),
			'uploadURL'				=> Router::url('/files', TRUE),
			'types'					=> $this->getTypes()
		];
		
		//If the user is not and admin
		if(!$this->Auth->isGroup(['admin'])) {
			//Only admins can delete or rename directories
			$default['access']['dirs'] = ['create' => TRUE, 'delete' => FALSE, 'rename' => FALSE];
			//Only admins can delete, move or rename files
			$default['access']['files'] = [
				'upload'	=> TRUE,
				'delete'	=> FALSE,
				'copy'		=> TRUE,
				'move'		=> FALSE,
				'rename'	=> FALSE
			];
		}
		
		//Merges options from the configuration
		$options = am($default, empty(config('kcfinder')) ? [] : config('kcfinder'));

		return $this->request->session()->write('KCFINDER', $options);
	}
	
	/**
	 * Gets the files path
	 * @return string
	 */
	public function getFilesPath() {
		return WWW_ROOT.'files';
	}
	
	/**
	 * Gets the folders list
	 * @return array
	 * @uses getFilesPath()
	 */
	public function getFolders() {
		return array_values((new \Cake\Filesystem\Folder($this->getFilesPath()))->read(TRUE, TRUE))[0];
	}
	
	/**
	 * Gets the KCFinder path
	 * @return string
	 */
	public function getKcfinderPath() {
		return WWW_ROOT.'vendor'.DS.'kcfinder';
	}
	
	/**
	 * Gets the file types supported by KCFinder
	 * @return array
	 * @uses getFolders()
	 */
	public function getTypes() {
		//Each folder is a type
		foreach($this->getFolders() as $type)
			$types[$type] = '';
		
		//Adds the "images" type by default
		$types['images'] = '*img';
		
		return $types;
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered.
	 * 
	 * Configures KCFinder.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses configure()
	 */
	public function beforeRender(\Cake\Event\Event $event) {
		$this->configure();
	}
}