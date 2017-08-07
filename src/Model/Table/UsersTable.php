<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\User;
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
     * Called before request data is converted into entities
     * @param \Cake\Event\Event $event Event object
     * @param \ArrayObject $data Request data
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.16.1
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        //Prevents that a blank password is saved
        if ($options['validate'] === 'EmptyPassword' && isset($data['password']) && $data['password'] === '') {
            unset($data['password'], $data['password_repeat']);
        }
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['group_id'], 'Groups', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->isUnique(['email'], I18N_VALUE_ALREADY_USED));
        $rules->add($rules->isUnique(['username'], I18N_VALUE_ALREADY_USED));

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
        $query->where([sprintf('%s.active', $this->getAlias()) => true])
            ->where([sprintf('%s.banned', $this->getAlias()) => false]);

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
        $query->where([sprintf('%s.banned', $this->getAlias()) => true]);

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
        $query->where([sprintf('%s.active', $this->getAlias()) => false])
            ->where([sprintf('%s.banned', $this->getAlias()) => false]);

        return $query;
    }

    /**
     * Gets active users as list
     * @return Query $query Query object
     * @uses $cache
     */
    public function getActiveList()
    {
        return $this->find()
            ->select(['id', 'first_name', 'last_name'])
            ->where([sprintf('%s.active', $this->getAlias()) => true])
            ->order(['username' => 'ASC'])
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('id')
                    ->map(function (User $user) {
                        return $user->first_name . ' ' . $user->last_name;
                    });
            })
            ->cache(sprintf('active_%s_list', $this->getTable()), $this->cache);
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->belongsTo('Groups', ['className' => ME_CMS . '.UsersGroups'])
            ->setForeignKey('group_id')
            ->setJoinType('INNER');

        $this->hasMany('Posts', ['className' => ME_CMS . '.Posts'])
            ->setForeignKey('user_id');

        $this->hasMany('Tokens', ['className' => 'Tokens.Tokens'])
            ->setForeignKey('user_id');

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
            $query->where([sprintf('%s.username LIKE', $this->getAlias()) => sprintf('%%%s%%', $data['username'])]);
        }

        //"Group" field
        if (!empty($data['group']) && isPositive($data['group'])) {
            $query->where([sprintf('%s.group_id', $this->getAlias()) => $data['group']]);
        }

        //"Status" field
        if (!empty($data['status'])) {
            switch ($data['status']) {
                case 'active':
                    $query->where([
                        sprintf('%s.active', $this->getAlias()) => true,
                        sprintf('%s.banned', $this->getAlias()) => false,
                    ]);

                    break;
                case 'pending':
                    $query->where([sprintf('%s.active', $this->getAlias()) => false]);

                    break;
                case 'banned':
                    $query->where([sprintf('%s.banned', $this->getAlias()) => true]);

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
