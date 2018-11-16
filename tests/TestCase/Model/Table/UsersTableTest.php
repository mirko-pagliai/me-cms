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
namespace MeCms\Test\TestCase\Model\Table;

use App\Utility\Token as TokenCreator;
use Cake\Cache\Cache;
use MeCms\Model\Validation\UserValidator;
use MeCms\TestSuite\TableTestCase;
use Tokens\Model\Entity\Token;

/**
 * UsersTableTest class
 */
class UsersTableTest extends TableTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * @var array
     */
    protected static $example = [
        'group_id' => 1,
        'email' => 'example@test.com',
        'first_name' => 'Alfa',
        'last_name' => 'Beta',
        'username' => 'myusername',
        'password' => 'mypassword1!',
        'password_repeat' => 'mypassword1!',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Posts',
        'plugin.me_cms.Tokens',
        'plugin.me_cms.Users',
        'plugin.me_cms.UsersGroups',
    ];

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('users', $this->Table->cache);
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $this->loadFixtures();

        $entity = $this->Table->patchEntity($this->Table->get(1), self::$example, ['validate' => 'EmptyPassword']);
        $this->assertNotEmpty($entity->password);
        $this->assertNotEmpty($entity->password_repeat);

        $example = self::$example;
        $example['password'] = $example['password_repeat'] = '';

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
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $this->loadFixtures();

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

        $example = self::$example;
        $example['group_id'] = 999;

        $entity = $this->Table->newEntity($example);
        $expected = $expected + ['group_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]];
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals($expected, $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('users', $this->Table->getTable());
        $this->assertEquals('username', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Groups);
        $this->assertEquals('group_id', $this->Table->Groups->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Groups->getJoinType());
        $this->assertEquals(ME_CMS . '.UsersGroups', $this->Table->Groups->className());

        $this->assertHasMany($this->Table->Posts);
        $this->assertEquals('user_id', $this->Table->Posts->getForeignKey());
        $this->assertEquals(ME_CMS . '.Posts', $this->Table->Posts->className());

        $this->assertHasMany($this->Table->Tokens);
        $this->assertEquals('user_id', $this->Table->Tokens->getForeignKey());
        $this->assertEquals('Tokens.Tokens', $this->Table->Tokens->className());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);

        $this->assertInstanceOf(UserValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for the `hasMany` association with `Tokens`
     * @test
     */
    public function testHasManyTokens()
    {
        $this->loadFixtures();

        //Creates a token
        $token = (new TokenCreator)->create('testToken', ['user_id' => 4]);
        $tokens = $this->Table->findById(4)->contain('Tokens')->extract('tokens')->first();

        $this->assertEquals(1, count($tokens));
        $this->assertInstanceOf(Token::class, $tokens[0]);
        $this->assertEquals(4, $tokens[0]->user_id);
        $this->assertEquals($token, $tokens[0]->token);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->loadFixtures();

        $query = $this->Table->find('active');
        $this->assertStringEndsWith('FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query as $entity) {
            $this->assertTrue($entity->active && !$entity->banned);
        }
    }

    /**
     * Test for `findAuth()` method
     * @test
     */
    public function testFindAuth()
    {
        $this->loadFixtures();

        $query = $this->Table->find('auth');
        $this->assertStringEndsWith('FROM users Users INNER JOIN users_groups Groups ON Groups.id = (Users.group_id)', $query->sql());
    }

    /**
     * Test for `findBanned()` method
     * @test
     */
    public function testFindBanned()
    {
        $this->loadFixtures();

        $query = $this->Table->find('banned');
        $this->assertStringEndsWith('FROM users Users WHERE Users.banned = :c0', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query as $entity) {
            $this->assertTrue($entity->banned);
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $this->loadFixtures();

        $query = $this->Table->find('pending');
        $this->assertStringEndsWith('FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query as $entity) {
            $this->assertTrue(!$entity->active && !$entity->banned);
        }
    }

    /**
     * Test for `getActiveList()` method
     * @test
     */
    public function testGetActiveList()
    {
        $this->loadFixtures();

        $query = $this->Table->getActiveList();
        $this->assertContains('FROM users Users WHERE Users.active = :c0 ORDER BY username ASC', $query->sql());
        $this->assertNotEmpty($query->toArray());

        $fromCache = Cache::read('active_users_list', $this->Table->cache)->toArray();
        $this->assertEquals($fromCache, $query->toArray());
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $this->loadFixtures();

        $data = [
            'username' => 'test',
            'group' => 1,
            'status' => 'active',
        ];

        $query = $this->Table->queryFromFilter($this->Table->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2 AND Users.banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $data['status'] = 'pending';

        $query = $this->Table->queryFromFilter($this->Table->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c2']['value']);

        $data['status'] = 'banned';

        $query = $this->Table->queryFromFilter($this->Table->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.banned = :c2)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);
    }

    /**
     * Test for `queryFromFilter()` method, with invalid data
     * @test
     */
    public function testQueryFromFilterWithInvalidData()
    {
        $this->loadFixtures();

        $query = $this->Table->queryFromFilter($this->Table->find(), ['status' => 'invalid', 'username' => 'ab']);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }

    /**
     * Test for `validationDoNotRequirePresence()` method
     * @test
     */
    public function testValidationDoNotRequirePresence()
    {
        $this->loadFixtures();

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
        $this->assertEquals([
            'email_repeat' => ['compareWith' => 'Email addresses don\'t match'],
        ], $entity->getErrors());
    }

    /**
     * Test for `validationEmptyPassword()` method
     * @test
     */
    public function testValidationEmptyPassword()
    {
        $this->loadFixtures();

        $example = self::$example;
        $example['password'] = $example['password_repeat'] = '';

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
