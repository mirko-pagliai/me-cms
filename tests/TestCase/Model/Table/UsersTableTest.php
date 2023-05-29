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
     * @var array
     */
    protected static array $example = [
        'group_id' => 1,
        'email' => 'example@test.com',
        'first_name' => 'Alfa',
        'last_name' => 'Beta',
        'username' => 'my-username',
        'password' => 'Password1!',
        'password_repeat' => 'Password1!',
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
     * @test
     * @uses \MeCms\Model\Table\UsersTable::beforeMarshal()
     */
    public function testBeforeMarshal(): void
    {
        $User = $this->Table->patchEntity($this->Table->get(1), self::$example, ['validate' => 'EmptyPassword']);
        $this->assertNotEmpty($User->get('password'));
        $this->assertNotEmpty($User->get('password_repeat'));

        $example = ['password' => '', 'password_repeat' => ''] + self::$example;
        $User = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($User->getErrors());
        $this->assertFalse(property_exists($User, 'password'));
        $this->assertFalse(property_exists($User, 'password_repeat'));

        unset($example['password'], $example['password_repeat']);
        $User = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($User->getErrors());
        $this->assertFalse(property_exists($User, 'password'));
        $this->assertFalse(property_exists($User, 'password_repeat'));
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::_initializeSchema()
     */
    public function testInitializeSchema(): void
    {
        $this->assertSame('json', $this->Table->getSchema()->getColumnType('last_logins'));
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $expected = [
            'email' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'username' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ];

        $User = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($User));

        //Saves again the same entity
        $User = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($User));
        $this->assertEquals($expected, $User->getErrors());

        $User = $this->Table->newEntity(['group_id' => 999] + self::$example);
        $expected = $expected + ['group_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]];
        $this->assertFalse($this->Table->save($User));
        $this->assertEquals($expected, $User->getErrors());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::initialize()
     */
    public function testInitialize(): void
    {
        $this->assertEquals('users', $this->Table->getTable());
        $this->assertEquals('username', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->UsersGroups);
        $this->assertEquals('group_id', $this->Table->UsersGroups->getForeignKey());
        $this->assertEquals('INNER', $this->Table->UsersGroups->getJoinType());
        $this->assertSame('group', $this->Table->UsersGroups->getProperty());

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
     * @test
     * @uses \MeCms\Model\Table\UsersTable::findActive()
     */
    public function testFindActive(): void
    {
        $query = $this->Table->find('active');
        $this->assertStringEndsWith('FROM users Users WHERE (active = :c0 AND banned = :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::findAuth()
     */
    public function testFindAuth(): void
    {
        $query = $this->Table->find('auth');
        $this->assertStringEndsWith('FROM users Users INNER JOIN users_groups UsersGroups ON UsersGroups.id = Users.group_id', $query->sql());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::findBanned()
     */
    public function testFindBanned(): void
    {
        $query = $this->Table->find('banned');
        $this->assertStringEndsWith('FROM users Users WHERE banned = :c0', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::findPending()
     */
    public function testFindPending(): void
    {
        $query = $this->Table->find('pending');
        $this->assertStringEndsWith('FROM users Users WHERE (active = :c0 AND banned = :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::getActiveList()
     */
    public function testGetActiveList(): void
    {
        $query = $this->Table->getActiveList();
        $this->assertStringEndsWith('FROM users Users WHERE active = :c0 ORDER BY username ASC', $query->sql());
        $this->assertNotEmpty($query->toArray());
        $fromCache = Cache::read('active_users_list', $this->Table->getCacheName())->toArray();
        $this->assertEquals($fromCache, $query->toArray());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::queryFromFilter()
     */
    public function testQueryFromFilter(): void
    {
        $data = [
            'username' => 'test',
            'group' => 1,
            'status' => 'active',
        ];

        $query = $this->Table->queryFromFilter($this->Table->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND active = :c2 AND banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'pending'] + $data);
        $this->assertStringEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND active = :c2 AND banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'banned'] + $data);
        $this->assertStringEndsWith('FROM users Users WHERE (username like :c0 AND group_id = :c1 AND banned = :c2)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);

        //With invalid data
        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'invalid', 'username' => 'ab']);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::validationDoNotRequirePresence()
     */
    public function testValidationDoNotRequirePresence(): void
    {
        $example = ['email' => 'example@test.com'];
        $User = $this->Table->newEntity($example);
        $this->assertNotEmpty($User->getErrors());

        $User = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($User->getErrors());

        $example['email_repeat'] = $example['email'];
        $User = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($User->getErrors());

        $example['email_repeat'] = $example['email'] . 'aaa';
        $User = $this->Table->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEquals(['email_repeat' => ['compareWith' => 'Email addresses don\'t match']], $User->getErrors());
    }

    /**
     * @test
     * @uses \MeCms\Model\Table\UsersTable::validationEmptyPassword()
     */
    public function testValidationEmptyPassword(): void
    {
        $example = ['password' => '', 'password_repeat' => ''] + self::$example;
        $expected = [
            'password' => ['_empty' => 'This field cannot be left empty'],
            'password_repeat' => ['_empty' => 'This field cannot be left empty'],
        ];

        $User = $this->Table->newEntity($example);
        $this->assertEquals($expected, $User->getErrors());

        $User = $this->Table->patchEntity($this->Table->get(1), $example);
        $this->assertEquals($expected, $User->getErrors());

        $User = $this->Table->patchEntity($this->Table->get(1), $example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($User->getErrors());
    }
}
