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
namespace MeCms\Shell;

use MeTools\Console\Shell;

/**
 * Applies updates
 */
class UpdateShell extends Shell {
	/**
	 * Database connection
	 * @see initialize()
	 * @var resource 
	 */
	protected $connection;
	
	/**
	 * Now for MySql
	 * @see initialize()
	 * @var string 
	 */
	protected $now;

	/**
	 * Initialize
	 * @uses $connection
	 */
	public function initialize() {
        parent::initialize();
		
		//Gets database connection
		$this->connection = \Cake\Datasource\ConnectionManager::get('default');
		
		//Sets now for MySql
		$this->now = (new \Cake\I18n\Time)->now()->i18nFormat(FORMAT_FOR_MYSQL);
	}
	
	/**
	 * Updates to 2.1.9 version
	 * @uses $connection
	 */
	public function to2v1v9() {
		$this->loadModel('MeCms.Banners');
		$this->loadModel('MeCms.Photos');
		
		//Adds "created" and "modified" field to the banners table and sets the default value
		$this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `click_count`, ADD `modified` DATETIME NULL AFTER `created`;', $this->Banners->table()));
		$this->Banners->query()->update()->set(['created' => $this->now, 'modified' => $this->now])->execute();
		
		//Adds "modified" field to the photos table and sets the default value
		$this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->Photos->table()));
		$this->Photos->query()->update()->set(['modified' => $this->now])->execute();
	}
	
	/**
	 * Updates to 2.1.8 version
	 * @uses $connection
	 */
	public function to2v1v8() {
		$this->loadModel('MeCms.Photos');
		$this->loadModel('MeCms.Tags');
		
		//Deletes all unused tags
		$this->Tags->deleteAll(['post_count' => 0]);
				
		//For each tag, it replaces the hyphen with space
		foreach($this->Tags->find()->toArray() as $tag)
			$this->Tags->query()->update()
				->set(['tag' => str_replace('-', ' ', $tag->tag)])
				->where(['id' => $tag->id])
				->execute();
		
		//Adds "created" field to the photos table and sets the default value
		$this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL DEFAULT NULL AFTER `description`;', $this->Photos->table()));
		$this->Photos->query()->update()->set(['created' => $this->now])->execute();
	}
	
	/**
	 * Updates to 2.1.7 version
	 * @uses $connection
	 */
	public function to2v1v7() {
		$this->loadModel('MeCms.Tags');
		
		$this->connection->execute(sprintf('ALTER TABLE `%s` CHANGE `tag` `tag` VARCHAR(30) NOT NULL;', $this->Tags->table()));
	}
	
	/**
	 * Gets the option parser instance and configures it.
	 * @return ConsoleOptionParser
	 * @uses MeTools\Shell\InstallShell::getOptionParser()
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		
		return $parser->addSubcommands([
			'to2v1v9' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.9')],
			'to2v1v8' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.8')],
			'to2v1v7' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.7')]
		]);
	}
}