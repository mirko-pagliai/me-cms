<?php
declare(strict_types=1);

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
use Cake\ORM\Query as CakeQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\User;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\PostsTable;
use MeCms\Model\Table\TokensTable;
use MeCms\Model\Table\UsersGroupsTable;
use MeCms\Model\Validation\UserValidator;
use MeCms\ORM\Query;

/**
 * Users model
 * @property \Cake\ORM\Association\BelongsTo $Groups
 * @property \Cake\ORM\Association\HasMany $Posts
 * @property \Cake\ORM\Association\HasMany $Tokens
 * @method findByActiveAndBanned(bool $isActive, bool $isBanned)
 * @method findActiveByEmail(string $email)
 * @method findByUsername(string $username)
 * @method findPendingByEmail(string $email)
 */
class UsersTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'users';

    /**
     * Called before request data is converted into entities
     * @param \Cake\Event\Event $event Event object
     * @param \ArrayObject $data Request data
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.16.1
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options): void
    {
        parent::beforeMarshal($event, $data, $options);

        //Prevents that a blank password is saved
        if ($options['validate'] === 'EmptyPassword' && isset($data['password']) && !$data['password']) {
            unset($data['password'], $data['password_repeat']);
        }
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->existsIn(['group_id'], 'Groups', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['email'], I18N_VALUE_ALREADY_USED))
            ->add($rules->isUnique(['username'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findActive(Query $query): Query
    {
        return $query->where(['active' => true, 'banned' => false]);
    }

    /**
     * "auth" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     * @since 2.25.1
     */
    public function findAuth(Query $query): Query
    {
        return $query->contain([$this->Groups->getAlias() => ['fields' => ['name']]]);
    }

    /**
     * "banned" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findBanned(Query $query): Query
    {
        return $query->where(['banned' => true]);
    }

    /**
     * "pending" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findPending(Query $query): Query
    {
        return $query->where(['active' => false, 'banned' => false]);
    }

    /**
     * Gets active users as list
     * @return \Cake\ORM\Query $query Query object
     */
    public function getActiveList(): CakeQuery
    {
        return $this->find()
            ->select(['id', 'first_name', 'last_name'])
            ->where(['active' => true])
            ->orderAsc('username')
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('id')->map(function (User $user): string {
                    return $user->get('first_name') . ' ' . $user->get('last_name');
                });
            })
            ->cache(sprintf('active_%s_list', $this->getTable()));
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->belongsTo('Groups', ['className' => UsersGroupsTable::class])
            ->setForeignKey('group_id')
            ->setJoinType('INNER');

        $this->hasMany('Posts', ['className' => PostsTable::class])->setForeignKey('user_id');
        $this->hasMany('Tokens', ['className' => TokensTable::class])->setForeignKey('user_id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Groups' => ['user_count']]);

        $this->_validatorClass = UserValidator::class;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data (`$this->getRequest()->getQueryParams()`)
     * @return \Cake\ORM\Query $query Query object
     */
    public function queryFromFilter(CakeQuery $query, array $data = []): CakeQuery
    {
        $query = parent::queryFromFilter($query, $data);

        //"Username" field
        if (!empty($data['username']) && strlen($data['username']) > 2) {
            $query->where(['username LIKE' => '%' . $data['username'] . '%']);
        }

        //"Group" field
        if (!empty($data['group']) && is_positive($data['group'])) {
            $query->where(['group_id' => $data['group']]);
        }

        //"Status" field
        if (!empty($data['status']) && in_array($data['status'], ['active', 'pending', 'banned'])) {
            $query->find($data['status']);
        }

        return $query;
    }

    /**
     * Validation "do not require presence".
     *
     * This validator doesn't require the presence of fields.
     * @param \MeCms\Model\Validation\UserValidator $validator Validator instance
     * @return \MeCms\Model\Validation\UserValidator
     */
    public function validationDoNotRequirePresence(UserValidator $validator): UserValidator
    {
        //No field is required
        foreach (array_keys(iterator_to_array($validator->getIterator())) as $field) {
            $validator->requirePresence($field, false);
        }

        return $validator;
    }

    /**
     * Validation "empty password".
     *
     * This validator allows passwords are empty.
     * @param \MeCms\Model\Validation\UserValidator $validator Validator instance
     * @return \MeCms\Model\Validation\UserValidator
     */
    public function validationEmptyPassword(UserValidator $validator): UserValidator
    {
        return $validator->allowEmptyString('password')->allowEmptyString('password_repeat');
    }
}
