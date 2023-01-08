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

namespace MeCms\Test\TestCase\Model\Table;

use App\Utility\Token as TokenCreator;
use Cake\Cache\Cache;
use MeCms\Model\Validation\UserValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * UsersTableTest class
 * @property \MeCms\Model\Table\UsersTable $Table
 */
class UsersTableTest extends TableTestCase
{
    /**
     * @var array<string, string|int>
     */
    protected static array $example = [
        'group_id' => 1,
        'email' => 'example@test.com',
        'first_name' => 'Alfa',
        'last_name' => 'Beta',
        'username' => 'my-username',
        'password' => 'my-password1!',
        'password_repeat' => 'my-password1!',
    ];

    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.Tokens',
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test for event methods
     * @uses \MeCms\Model\Table\UsersTable::beforeMarshal()
     * @test
     */
    public function testEventMethods(): void
    {
        $entity = $this->Table->patchEntity($this->Table->get(1), self::$example, ['validate' => 'EmptyPassword']);
        $this->assertNotEmpty($entity->get('password'));
        $this->assertNotEmpty($entity->get('password_repeat'));

        $example = ['password' => '', 'password_repeat' => ''] + self::$example;
        $entity = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->getErrors());
        $this->assertObjectNotHasAttribute('password', $entity);
        $this->assertObjectNotHasAttribute('password_repeat', $entity);

        unset($example['password'], $example['password_repeat']);
        $entity = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->getErrors());
        $this->assertObjectNotHasAttribute('password', $entity);
        $this->assertObjectNotHasAttribute('password_repeat', $entity);
    }

    /**
     * Test for `_initializeSchema()` method
     * @test
     */
    public function testInitializeSchema(): void
    {
        $this->assertSame('json', $this->Table->getSchema()->getColumnType('last_logins'));
    }

    /**
     * Test for `buildRules()` method
     * @uses \MeCms\Model\Table\UsersTable::buildRules()
     * @test
     */
    public function testBuildRules(): void
    {
        $expected = [
            'email' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'username' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ];

        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Table->newEntity(['group_id' => 999] + self::$example);
        $expected = $expected + ['group_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]];
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals($expected, $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @uses \MeCms\Model\Table\UsersTable::initialize()
     * @test
     */
    public function testInitialize(): void
    {
        $this->assertEquals('users', $this->Table->getTable());
        $this->assertEquals('username', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Groups);
        $this->assertEquals('group_id', $this->Table->Groups->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Groups->getJoinType());

        $this->assertHasMany($this->Table->Posts);
        $this->assertEquals('user_id', $this->Table->Posts->getForeignKey());

        $this->assertHasMany($this->Table->Tokens);
        $this->assertEquals('user_id', $this->Table->Tokens->getForeignKey());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);

        $this->assertInstanceOf(UserValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for associations
     * @test
     */
    public function testAssociations(): void
    {
        $token = (new TokenCreator())->create('testToken', ['user_id' => 4]);
        $tokens = $this->Table->findById(4)->contain('Tokens')->all()->extract('tokens')->first();
        $this->assertEquals(1, count($tokens));
        $this->assertEquals(4, $tokens[0]->get('user_id'));
        $this->assertEquals($token, $tokens[0]->get('token'));
    }

    /**
     * Test for `find()` methods
     * @uses \MeCms\Model\Table\UsersTable::findActive()
     * @uses \MeCms\Model\Table\UsersTable::findAuth()
     * @uses \MeCms\Model\Table\UsersTable::findBanned()
     * @uses \MeCms\Model\Table\UsersTable::findPending()
     * @test
     */
    public function testFindMethods(): void
    {
        $query = $this->Table->find('active');
        $this->assertSqlEndsWith('FROM users Users WHERE (active = :c0 AND banned = :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);

        $query = $this->Table->find('banned');
        $this->assertSqlEndsWith('FROM users Users WHERE banned = :c0', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);

        $query = $this->Table->find('pending');
        $this->assertSqlEndsWith('FROM users Users WHERE (active = :c0 AND banned = :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);

        $query = $this->Table->find('auth');
        $this->assertSqlEndsWith('FROM `users` `Users` INNER JOIN `users_groups` `Groups` ON `Groups`.`id` = `Users`.`group_id` WHERE (`active` = :c0 AND `banned` = :c1)', $query->sql());
    }

    /**
     * Test for `getActiveList()` method
     * @uses \MeCms\Model\Table\UsersTable::getActiveList()
     * @test
     */
    public function testGetActiveList(): void
    {
        $query = $this->Table->getActiveList();
        $this->assertSqlEndsWith('FROM users Users WHERE active = :c0 ORDER BY username ASC', $query->sql());
        $this->assertNotEmpty($query->toArray());
        $fromCache = Cache::read('active_users_list', $this->Table->getCacheName())->toArray();
        $this->assertEquals($fromCache, $query->toArray());
    }

    /**
     * Test for `queryFromFilter()` method
     * @uses \MeCms\Model\Table\UsersTable::queryFromFilter()
     * @test
     */
    public function testQueryFromFilter(): void
    {
        $data = [
            'username' => 'test',
            'group' => 1,
            'status' => 'active',
        ];

        $query = $this->Table->queryFromFilter($this->Table->find(), $data);
        $this->assertSqlEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND active = :c2 AND banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'pending'] + $data);
        $this->assertSqlEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND active = :c2 AND banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'banned'] + $data);
        $this->assertSqlEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND banned = :c2)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);

        //With invalid data
        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'invalid', 'username' => 'ab']);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }

    /**
     * Test for `validationDoNotRequirePresence()` method
     * @uses \MeCms\Model\Table\UsersTable::validationDoNotRequirePresence()
     * @test
     */
    public function testValidationDoNotRequirePresence(): void
    {
        $example = ['email' => 'example@test.com'];
        $entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($entity->getErrors());

        $entity = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->getErrors());

        $example['email_repeat'] = $example['email'];
        $entity = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->getErrors());

        $example['email_repeat'] = $example['email'] . 'aaa';
        $entity = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEquals(['email_repeat' => ['compareWith' => 'Email addresses don\'t match']], $entity->getErrors());
    }

    /**
     * Test for `validationEmptyPassword()` method
     * @uses \MeCms\Model\Table\UsersTable::validationEmptyPassword()
     * @test
     */
    public function testValidationEmptyPassword(): void
    {
        $example = ['password' => '', 'password_repeat' => ''] + self::$example;
        $expected = [
            'password' => ['_empty' => 'This field cannot be left empty'],
            'password_repeat' => ['_empty' => 'This field cannot be left empty'],
        ];

        $entity = $this->Table->newEntity($example);
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Table->patchEntity($this->Table->get(1), $example);
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->getErrors());
    }
}
