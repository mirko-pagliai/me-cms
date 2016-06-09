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

use MeCms\Shell\BaseUpdateShell;

/**
 * Applies updates
 */
class UpdateShell extends BaseUpdateShell {
    /**
	 * Updates to 2.10.0 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
     */
    public function to2v10v0() {
		$this->loadModel('MeCms.Photos');
        
        //Adds "active" field to the photos table and sets the default value
        if(!$this->_checkColumn('active', $this->Photos->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `active` BOOLEAN NOT NULL DEFAULT TRUE AFTER `description`;', $this->Photos->table()));
            $this->Photos->query()->update()->set(['active' => TRUE])->execute();
        }
    }
    
    /**
	 * Updates to 2.7.0 version
     */
	public function to2v7v0() {
        $this->dispatchShell('MeCms.install', 'createVendorsLinks');
        
        @unlink(WWW_ROOT.'vendor'.DS.'jquery-cookie');
    }
    
	/**
	 * Updates to 2.6.0 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
	 */
	public function to2v6v0() {
		$this->loadModel('MeCms.BannersPositions');
		$this->loadModel('MeCms.PhotosAlbums');
		$this->loadModel('MeCms.PostsCategories');
		$this->loadModel('MeCms.Tags');
		$this->loadModel('MeCms.UsersGroups');
        
        //Adds "created" field to the banners positions table and sets the default value
        if(!$this->_checkColumn('created', $this->BannersPositions->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `banner_count`;', $this->BannersPositions->table()));
            $this->BannersPositions->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the banners positions table and sets the default value
        if(!$this->_checkColumn('modified', $this->BannersPositions->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->BannersPositions->table()));
            $this->BannersPositions->query()->update()->set(['modified' => $this->now])->execute();
        }
        
        //Adds "created" field to the photos albums table and sets the default value
        if(!$this->_checkColumn('created', $this->PhotosAlbums->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `photo_count`;', $this->PhotosAlbums->table()));
            $this->PhotosAlbums->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the photos albums table and sets the default value
        if(!$this->_checkColumn('modified', $this->PhotosAlbums->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->PhotosAlbums->table()));
            $this->PhotosAlbums->query()->update()->set(['modified' => $this->now])->execute();
        }
        
        //Adds "created" field to the posts categories table and sets the default value
        if(!$this->_checkColumn('created', $this->PostsCategories->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `post_count`;', $this->PostsCategories->table()));
            $this->PostsCategories->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the posts categories table and sets the default value
        if(!$this->_checkColumn('modified', $this->PostsCategories->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->PostsCategories->table()));
            $this->PostsCategories->query()->update()->set(['modified' => $this->now])->execute();
        }
        
        //Adds "created" field to the tags table and sets the default value
        if(!$this->_checkColumn('created', $this->Tags->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `post_count`;', $this->Tags->table()));
            $this->Tags->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the tags table and sets the default value
        if(!$this->_checkColumn('modified', $this->Tags->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->Tags->table()));
            $this->Tags->query()->update()->set(['modified' => $this->now])->execute();
        }
        
        //Adds "created" field to the users groups table and sets the default value
        if(!$this->_checkColumn('created', $this->UsersGroups->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `user_count`;', $this->UsersGroups->table()));
            $this->UsersGroups->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the users groups table and sets the default value
        if(!$this->_checkColumn('modified', $this->UsersGroups->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->UsersGroups->table()));
            $this->UsersGroups->query()->update()->set(['modified' => $this->now])->execute();
        }
    }
    
	/**
	 * Updates to 2.2.1 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
	 */
	public function to2v2v1() {
		$this->loadModel('MeCms.Tags');
		
		//For each tag, it replaces the hyphen with space
		foreach($this->Tags->find()->where(['tag LIKE' => '%-%'])->toArray() as $tag) {
			$this->Tags->query()->update()
				->set(['tag' => str_replace('-', ' ', $tag->tag)])
				->where(['id' => $tag->id])
				->execute();
        }
	}
	
	/**
	 * Updates to 2.1.9 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
	 */
	public function to2v1v9() {
		$this->loadModel('MeCms.Banners');
		$this->loadModel('MeCms.Photos');
		
        //Adds "created" field to the banners table and sets the default value
        if(!$this->_checkColumn('created', $this->Banners->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL AFTER `click_count`;', $this->Banners->table()));
            $this->Banners->query()->update()->set(['created' => $this->now])->execute();
        }
        
        //Adds "modified" field to the banners table and sets the default value
        if(!$this->_checkColumn('modified', $this->Banners->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->Banners->table()));
            $this->Banners->query()->update()->set(['modified' => $this->now])->execute();
        }
        
        //Adds "modified" field to the photos table and sets the default value
        if(!$this->_checkColumn('modified', $this->Photos->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `modified` DATETIME NULL AFTER `created`;', $this->Photos->table()));
            $this->Photos->query()->update()->set(['modified' => $this->now])->execute();
        }
	}
	
	/**
	 * Updates to 2.1.8 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
     * @uses MeCms\Shell\BaseUpdateShell::_checkColumn()
	 */
	public function to2v1v8() {
		$this->loadModel('MeCms.Photos');
		$this->loadModel('MeCms.Tags');
		
		//Deletes all unused tags
		$this->Tags->deleteAll(['post_count' => 0]);
				
		//For each tag, it replaces the hyphen with space
		foreach($this->Tags->find()->toArray() as $tag) {
			$this->Tags->query()->update()
				->set(['tag' => str_replace('-', ' ', $tag->tag)])
				->where(['id' => $tag->id])
				->execute();
        }
		
		//Adds "created" field to the photos table and sets the default value
        if(!$this->_checkColumn('created', $this->Photos->table())) {
            $this->connection->execute(sprintf('ALTER TABLE `%s` ADD `created` DATETIME NULL DEFAULT NULL AFTER `description`;', $this->Photos->table()));
            $this->Photos->query()->update()->set(['created' => $this->now])->execute();
        }
	}
	
	/**
	 * Updates to 2.1.7 version
	 * @uses MeCms\Shell\BaseUpdateShell::$connection
	 */
	public function to2v1v7() {
		$this->loadModel('MeCms.Tags');
		
		$this->connection->execute(sprintf('ALTER TABLE `%s` CHANGE `tag` `tag` VARCHAR(30) NOT NULL;', $this->Tags->table()));
	}
	
	/**
	 * Gets the option parser instance and configures it.
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		
		return $parser->addSubcommands([
            'to2v10v0' => ['help' => __d('me_cms', 'Updates to {0} version', '2.10.0')],
            'to2v7v0' => ['help' => __d('me_cms', 'Updates to {0} version', '2.7.0')],
            'to2v6v0' => ['help' => __d('me_cms', 'Updates to {0} version', '2.6.0')],
			'to2v2v1' => ['help' => __d('me_cms', 'Updates to {0} version', '2.2.1')],
			'to2v1v9' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.9')],
			'to2v1v8' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.8')],
			'to2v1v7' => ['help' => __d('me_cms', 'Updates to {0} version', '2.1.7')]
		]);
	}
}