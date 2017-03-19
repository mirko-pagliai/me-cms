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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\UserValidator;

/**
 * Users model
 * @property \Cake\ORM\Association\BelongsTo $Groups
 * @property \Cake\ORM\Association\HasMany $Posts
 * @property \Cake\ORM\Association\HasMany $Tokens
 * @property \Cake\ORM\Association\HasMany $YoutubeVideos
 */
class UsersTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'users';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['group_id'], 'Groups', __d('me_cms', 'You have to select a valid option')));
        $rules->add($rules->isUnique(['email'], __d('me_cms', 'This value is already used')));
        $rules->add($rules->isUnique(['username'], __d('me_cms', 'This value is already used')));

        return $rules;
    }

    /**
     * "Active" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        $query->where([
            sprintf('%s.active', $this->alias()) => true,
            sprintf('%s.banned', $this->alias()) => false,
        ]);

        return $query;
    }

    /**
     * "Banned" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findBanned(Query $query, array $options)
    {
        $query->where([sprintf('%s.banned', $this->alias()) => true]);

        return $query;
    }

    /**
     * "Pending" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findPending(Query $query, array $options)
    {
        $query->where([
            sprintf('%s.active', $this->alias()) => false,
            sprintf('%s.banned', $this->alias()) => false,
        ]);

        return $query;
    }

    /**
     * Gets the active users list
     * @return array List
     * @uses $cache
     */
    public function getActiveList()
    {
        return $this->find('list')
            ->where([sprintf('%s.active', $this->alias()) => true])
            ->cache('active_users_list', $this->cache)
            ->order(['username' => 'ASC'])
            ->toArray();
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('username');
        $this->primaryKey('id');

        $this->belongsTo('Groups', [
            'foreignKey' => 'group_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.UsersGroups',
        ]);
        $this->hasMany('Posts', [
            'foreignKey' => 'user_id',
            'className' => 'MeCms.Posts',
        ]);
        $this->hasMany('Tokens', [
            'foreignKey' => 'user_id',
            'className' => 'Tokens.Tokens',
        ]);

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Groups' => ['user_count']]);

        $this->_validatorClass = '\MeCms\Model\Validation\UserValidator';
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->getQuery())
     * @return Query $query Query object
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Username" field
        if (!empty($data['username']) && strlen($data['username']) > 2) {
            $query->where([sprintf('%s.username LIKE', $this->alias()) => sprintf('%%%s%%', $data['username'])]);
        }

        //"Group" field
        if (!empty($data['group']) && isPositive($data['group'])) {
            $query->where([sprintf('%s.group_id', $this->alias()) => $data['group']]);
        }

        //"Status" field
        if (!empty($data['status'])) {
            switch ($data['status']) {
                case 'active':
                    $query->where([
                        sprintf('%s.active', $this->alias()) => true,
                        sprintf('%s.banned', $this->alias()) => false,
                    ]);

                    break;
                case 'pending':
                    $query->where([sprintf('%s.active', $this->alias()) => false]);

                    break;
                case 'banned':
                    $query->where([sprintf('%s.banned', $this->alias()) => true]);

                    break;
            }
        }

        return $query;
    }

    /**
     * Validation "do not require presence".
     *
     * This validator doesn't require the presence of fields.
     * @param UserValidator $validator Validator instance
     * @return UserValidator
     */
    public function validationDoNotRequirePresence(UserValidator $validator)
    {
        //No field is required
        foreach ($validator->getIterator() as $field => $rules) {
            $validator->requirePresence($field, false);
        }

        return $validator;
    }

    /**
     * Validation "empty password".
     *
     * This validator allows passwords are empty.
     * @param UserValidator $validator Validator instance
     * @return UserValidator
     */
    public function validationEmptyPassword(UserValidator $validator)
    {
        //Allows empty passwords
        $validator->allowEmpty('password');
        $validator->allowEmpty('password_repeat');

        return $validator;
    }
}
