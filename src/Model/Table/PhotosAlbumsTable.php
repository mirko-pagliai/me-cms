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
namespace MeCms\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\PhotosAlbum;
use MeCms\Model\Table\AppTable;
use MeCms\Utility\PhotoFile;

/**
 * PhotosAlbums model
 */
class PhotosAlbumsTable extends AppTable {
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 * @uses MeCms\Utility\PhotoFile::deleteFolder()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		//Deletes the folder
		PhotoFile::deleteFolder($entity->id);
		
		Cache::clear(FALSE, 'photos');		
	}
	
	/**
	 * Called after an entity is saved
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 * @uses MeCms\Utility\PhotoFile::createFolder()
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		//Creates the folder
		if($entity->isNew())
			PhotoFile::createFolder($entity->id);
		
		Cache::clear(FALSE, 'photos');
	}
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findActive(Query $query, array $options) {
        $query->where([
			sprintf('%s.active', $this->alias())		=> TRUE,
			sprintf('%s.photo_count >', $this->alias())	=> 0
		]);
		
        return $query;
    }

	/**
	 * Gets the albums list
	 * @return array List
	 */
	public function getList() {
		return $this->find('list')
			->cache('albums_list', 'photos')
			->toArray();
	}
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('photos_albums');
        $this->displayField('title');
        $this->primaryKey('id');
		
        $this->hasMany('Photos', [
            'foreignKey' => 'album_id',
            'className' => 'MeCms.Photos'
        ]);
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\PhotosAlbumValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\PhotosAlbumValidator;
    }
}