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
use MeCms\Model\Entity\Banner;
use MeCms\Model\Table\AppTable;
use MeCms\Utility\BannerFile;

/**
 * Banners model
 */
class BannersTable extends AppTable {	
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 * @uses MeCms\Utility\BannerFile::delete()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		//Deletes the file
		BannerFile::delete($entity->filename);
		
		Cache::clear(FALSE, 'banners');		
	}
	
	/**
	 * Called after an entity is saved
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 * @uses MeCms\Utility\BannerFile::save()
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		Cache::clear(FALSE, 'banners');
	}

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->existsIn(['position_id'], 'Positions'));
        return $rules;
    }
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findActive(Query $query, array $options) {		
        $query->where([sprintf('%s.active', $this->alias()) => TRUE]);
		
        return $query;
    }
	
	/**
	 * Gets conditions from a filter form
	 * @param array $query Query (`$this->request->query`)
	 * @return array Conditions
	 * @uses MeCms\Model\Table\AppTable::fromFilter()
	 */
	public function fromFilter(array $query) {
		if(empty($query))
			return [];
		
		$conditions = parent::fromFilter($query);
		
		//"Position" field
		if(!empty($query['position']))
			$conditions[sprintf('%s.position_id', $this->alias())] = $query['position'];
		
		return empty($conditions) ? [] : $conditions;
	}
	
    /**
     * Initialize method
     * @param array $config The table configuration
     */
    public function initialize(array $config) {
        $this->table('banners');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('CounterCache', ['Positions' => ['banner_count']]);
        $this->belongsTo('Positions', [
            'foreignKey' => 'position_id',
            'className' => 'MeCms.BannersPositions'
        ]);
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\BannerValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\BannerValidator;
    }
}