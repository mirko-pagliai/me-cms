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
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\UsersGroup;
use MeCms\Model\Table\AppTable;

/**
 * UsersGroups model
 */
class UsersGroupsTable extends AppTable {
	/**
	 * Name of the configuration to use for this table
	 * @var string|array
	 */
	public $cache = 'users';
	
	/**
	 * Gets the groups list
	 * @return array List
	 * @uses $cache
	 */
	public function getList() {
		return $this->find('list')
			->cache('groups_list', $this->cache)
			->toArray();
	}
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('users_groups');
        $this->displayField('label');
        $this->primaryKey('id');
		
        $this->hasMany('Users', [
            'foreignKey' => 'group_id',
            'className' => 'MeCms.Users',
        ]);
        
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\UsersGroupValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\UsersGroupValidator;
		
    }
}