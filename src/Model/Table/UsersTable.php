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
use MeCms\Model\Entity\User;
use MeCms\Model\Table\AppTable;

/**
 * Users model
 */
class UsersTable extends AppTable {
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		Cache::clear(FALSE, 'users');		
	}
	
	/**
	 * Called after an entity is saved.
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses Cake\Cache\Cache::clear()
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		Cache::clear(FALSE, 'users');
	}

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['group_id'], 'Groups'));
        return $rules;
    }
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findActive(Query $query, array $options) {
        $query->where([
			sprintf('%s.active', $this->alias()) => TRUE,
			sprintf('%s.banned', $this->alias()) => FALSE
		]);
		
        return $query;
    }
	
	/**
	 * "Banned" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findBanned(Query $query, array $options) {
        $query->where([sprintf('%s.banned', $this->alias()) => TRUE]);
		
        return $query;
    }
	
	/**
	 * "Pending" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findPending(Query $query, array $options) {
        $query->where([
			sprintf('%s.active', $this->alias()) => FALSE,
			sprintf('%s.banned', $this->alias()) => FALSE
		]);
		
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
		
		//"Username" field
		if(!empty($query['username'])) {
			$conditions[sprintf('%s.username LIKE', $this->alias())] = sprintf('%%%s%%', $query['username']);
		}
		
		//"Status" field
		if(!empty($query['status'])) {
			switch($query['status']) {
				case 'active':
					$conditions[sprintf('%s.active', $this->alias())] = TRUE;
					$conditions[sprintf('%s.banned', $this->alias())] = FALSE;
					break;
				case 'pending':
					$conditions[sprintf('%s.active', $this->alias())] = FALSE;
					break;
				case 'banned':
					$conditions[sprintf('%s.banned', $this->alias())] = TRUE;
					break;
			}
		}
		
		return empty($conditions) ? [] : $conditions;
	}
	
	/**
	 * Gets the active users list
	 * @return array List
	 */
	public function getActiveList() {
		return $this->find('list')
			->where(['active' => TRUE])
			->cache('active_users_list', 'users')
			->toArray();
	}
	
	/**
	 * Gets the users list
	 * @return array List
	 */
	public function getList() {
		return $this->find('list')
			->cache('users_list', 'users')
			->toArray();
	}
	
    /**
     * Initialize method
     * @param array $config The table configuration
     */
    public function initialize(array $config) {
        $this->table('users');
        $this->displayField('full_name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Groups' => ['user_count']]);
        $this->belongsTo('Groups', [
            'foreignKey' => 'group_id',
            'className' => 'MeCms.UsersGroups'
        ]);
        $this->hasMany('Posts', [
            'foreignKey' => 'user_id',
            'className' => 'MeCms.Posts'
        ]);
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\UserValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\UserValidator;
    }
	
	/**
	 * Validation "not unique"
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\UserValidator
	 * @see MeCms\Controller\UsersController::forgot_password()
	 */
	public function validationNotUnique(\Cake\Validation\Validator $validator) {
		$validator = new \MeCms\Model\Validation\UserValidator;
		
		//Username and email don't have to be unique 
		$validator->remove('username', 'unique')->remove('email', 'unique');
		
		//No field is required
		foreach($validator->getIterator() as $field => $value)
			$validator->requirePresence($field, FALSE);
		
		return $validator;
	}
	
	/**
	 * Validation "empty password"
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\UserValidator
	 * @see MeCms\Controller\Admin\UsersController::edit()
	 */
	public function validationEmptyPassword(\Cake\Validation\Validator $validator) {
		$validator = new \MeCms\Model\Validation\UserValidator;
		
		//Allow empty passwords
		$validator->allowEmpty('password');
		$validator->allowEmpty('password_repeat');
		
		return $validator;
	}
}