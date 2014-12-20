<?php
/**
 * InstallShell
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
 * @package		MeCms\Console\Command
 */

App::uses('MeToolsAppShell', 'MeTools.Console/Command');
App::uses('BannerManager', 'MeCms.Utility');
App::uses('PhotoManager', 'MeCms.Utility');

/**
 * Install shell.
 * It installs MeCms, creating the database schema, the first administrator user and the folders.
 */
class InstallShell extends MeToolsAppShell {
	/**
	 * Models
	 * @var array 
	 */
	public $uses = array('MeCms.User');
	
	/**
	 * Tasks
	 * @var array 
	 */
	public $tasks = array('MeTools.Database');
	
	/**
	 * Creates a folder.
	 * @param string $path Folder path
	 * @return bool TRUE if the directory has been created or if it already existed, otherwise FALSE
	 */
	private function _create_folder($path) {
		$create = TRUE;
		
		if(!is_writable($path)) {
			$folder = new Folder();
			if($create = (bool) @$folder->create($path, 0777))
				$this->out(sprintf('<success>%s</success>', sprintf('The directory %s has been created', $path)));
			else
				$this->out(sprintf('<warning>%s</warning>', sprintf('The directory %s has not been created. You have to create it manually', $path)));
		}
		
		return $create;
	}
	
	/**
	 * Creates the database schema and the first administrator user.
	 * @uses DatabaseTask::create()
	 */
	private function _install_database() {
		$this->out('Now you have to insert the data to create the administrator user');
		
		//Gets the necessary data to create the administrator user 
		$input = array();
		$input['username']			= $this->in('Username');
		$input['email']				= $this->in('Email');
		$input['password']			= $this->in('Password');
		$input['password_repeat']	= $this->in('Password repeat');
		$input['first_name']		= $this->in('First name');
		$input['last_name']			= $this->in('Last name');
		
		//Creates the database schema
		$this->Database->create('MeCms');
		
		//Creates the administrator user
		$this->User->create();
		if($this->User->save(array('User' => am($input, array('group_id' => 1)))))
			$this->out(sprintf('<success>%s</success>', 'The database schema and the administrator user have been created'));
		else {
			//Prints all errors
			foreach($this->User->validationErrors as $field => $errors)
				foreach($errors as $error)
					$this->out(sprintf('<error>Error:</error> "%s": %s', $field, $error));
			
			$this->error('the administrator user was not created. Try again');
		}
	}
	
	/**
	 * Creates folders.
	 * @uses _create_folder()
	 * @uses BannerManager::getFolder()
	 * @uses BannerManager::getTmpPath()
	 * @uses PhotoManager::getFolder()
	 * @uses PhotoManager::getTmpPath()
	 */
	private function _install_folders() {
		$cacheSettings = Cache::settings();
		
		$folders = array(
			BannerManager::getFolder(),
			BannerManager::getTmpPath(),
			PhotoManager::getFolder(),
			PhotoManager::getTmpPath(),
			$cacheSettings['path'],
			TMP.'thumbs'.DS.'photos',
			TMP.'thumbs'.DS.'remotes',
			TMP.'thumbs'.DS.'videos'
		);
		
		foreach($folders as $folder)
			$this->_create_folder($folder);
	}
	
	/**
	 * The main method.
	 * It installs MeCms, creating the database schema, adding the first administrator user and creating the folders.
	 * @uses _install_database()
	 * @uses _install_folders()
	 */
    public function main() {
		$this->out('This shell allows you to install MeCMS');
		$this->out('If you continue, the database will be completely erased and will create a new administrator user');

		//Asks the user whether to continue
		if($this->in('Continue?', array('y', 'n'), 'y') === 'n') {
			$this->out('Ok, I\'m exiting...');
			exit;
		}
		
		$this->out();
		
		//Creates folders
		$this->_install_folders();
		
		//Installs database
		$this->_install_database();
		
		$this->out();
		
		$this->out(sprintf('<success>%s</success>', 'MeCMS has been properly installed. Now you can login'));
    }
	
	/**
	 * Starts up the Shell and displays the welcome message.
	 * Allows for checking and configuring prior to command or main execution.
	 * @uses DatabaseTask::check()
	 */
	public function startup() {
		//Checks for database connection
		$this->Database->check();
	}
}

?>