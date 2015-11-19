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
use MeCms\Model\Entity\Photo;
use MeCms\Model\Table\AppTable;
use MeCms\Utility\PhotoFile;

/**
 * Photos model
 * @property \Cake\ORM\Association\BelongsTo $Albums
 */
class PhotosTable extends AppTable {
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 * @uses MeCms\Utility\PhotoFile::delete()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		//Deletes the file
		PhotoFile::delete($entity->filename, $entity->album_id);
		
		Cache::clear(FALSE, 'photos');
	}
	
	/**
	 * Called after an entity is saved
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		Cache::clear(FALSE, 'photos');
	}

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->existsIn(['album_id'], 'Albums'));
        return $rules;
    }
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 * @uses Cake\I18n\Time::i18nFormat()
	 */
	public function findActive(Query $query, array $options) {
		$query->matching('Albums', function ($q) {
			return $q->where(['active' => TRUE]);
		});
		
        return $query;
    }
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('photos');
        $this->displayField('id');
        $this->primaryKey('id');
		
        $this->belongsTo('Albums', [
            'foreignKey' => 'album_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.PhotosAlbums'
        ]);
		
        $this->addBehavior('CounterCache', ['Albums' => ['photo_count']]);
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\PhotoValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\PhotoValidator;
    }
}