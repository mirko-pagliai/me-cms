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

/**
 * Install shell.
 * It installs MeCms, creating the database schema and adding the first administrator user.
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
	 * The main method.
	 * It installs MeCms, creating the database schema and adding the first administrator user.
	 * @uses DatabaseTask::create()
	 */
    public function main() {
		$this->out('This shell allows you to install MeCMS');
		$this->out('If you continue, the database will be completely erased and will create a new administrator user');

		//Asks the user whether to continue
		if($this->in('Continue?', array('y', 'n'), 'y') === 'n') {
			$this->out('Ok, i\'m exiting...');
			exit;
		}
		
		$this->out();
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
		
		//Saves the administrator user
		$this->User->create();
		if($this->User->save(array('User' => am($input, array('group_id' => 1))))) {
			$this->out(sprintf('<success>%s</success>', 'The database schema and the administrator user have been created'));
			$this->out(sprintf('<success>%s</success>', 'MeCMS has been properly installed. Now you can login'));
		}
		else {
			//Prints all errors
			foreach($this->User->validationErrors as $field => $errors)
				foreach($errors as $error)
					$this->out(sprintf('<error>Error:</error> "%s": %s', $field, $error));
			
			$this->error('the admin user was not created. Try again');
		}
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