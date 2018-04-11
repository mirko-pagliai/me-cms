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

use App\Utility\Token;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

/**
 * UsersTableTest class
 */
class UsersTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.tokens',
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Users = TableRegistry::get(ME_CMS . '.Users');

        $this->example = [
            'group_id' => 1,
            'email' => 'example@test.com',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'username' => 'myusername',
            'password' => 'mypassword1!',
            'password_repeat' => 'mypassword1!',
        ];

        Cache::clear(false, $this->Users->cache);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('users', $this->Users->cache);
    }

    /**
     * Test for `beforeMarshal()` method
     * @test
     */
    public function testBeforeMarshal()
    {
        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertNotEmpty($entity->password);
        $this->assertNotEmpty($entity->password_repeat);

        $this->example['password'] = $this->example['password_repeat'] = '';

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->getErrors());
        $this->assertObjectNotHasAttribute('password', $entity);
        $this->assertObjectNotHasAttribute('password_repeat', $entity);

        unset($this->example['password'], $this->example['password_repeat']);

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
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
        $expected = [
            'email' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'username' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ];

        $entity = $this->Users->newEntity($this->example);
        $this->assertNotEmpty($this->Users->save($entity));

        //Saves again the same entity
        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals($expected, $entity->getErrors());

        $this->example['group_id'] = 999;

        $entity = $this->Users->newEntity($this->example);
        $this->assertFalse($this->Users->save($entity));
        $this->assertEquals(array_merge([
            'group_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
        ], $expected), $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('users', $this->Users->getTable());
        $this->assertEquals('username', $this->Users->getDisplayField());
        $this->assertEquals('id', $this->Users->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Users->Groups);
        $this->assertEquals('group_id', $this->Users->Groups->getForeignKey());
        $this->assertEquals('INNER', $this->Users->Groups->getJoinType());
        $this->assertEquals(ME_CMS . '.UsersGroups', $this->Users->Groups->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Users->Posts);
        $this->assertEquals('user_id', $this->Users->Posts->getForeignKey());
        $this->assertEquals(ME_CMS . '.Posts', $this->Users->Posts->className());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Users->Tokens);
        $this->assertEquals('user_id', $this->Users->Tokens->getForeignKey());
        $this->assertEquals('Tokens.Tokens', $this->Users->Tokens->className());

        $this->assertTrue($this->Users->hasBehavior('Timestamp'));
        $this->assertTrue($this->Users->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\UserValidator', [$this->Users->getValidator()]);
    }

    /**
     * Test for the `belongsTo` association with `UsersGroups`
     * @test
     */
    public function testBelongsToUsersGroups()
    {
        $user = $this->Users->findById(4)->contain('Groups')->first();

        $this->assertNotEmpty($user->group);
        $this->assertInstanceOf('MeCms\Model\Entity\UsersGroup', $user->group);
        $this->assertEquals(3, $user->group->id);
    }

    /**
     * Test for the `hasMany` association with `Posts`
     * @test
     */
    public function testHasManyPosts()
    {
        $user = $this->Users->findById(1)->contain('Posts')->first();

        $this->assertNotEmpty($user->posts);

        foreach ($user->posts as $post) {
            $this->assertInstanceOf('MeCms\Model\Entity\Post', $post);
            $this->assertEquals(1, $post->user_id);
        }
    }

    /**
     * Test for the `hasMany` association with `Tokens`
     * @test
     */
    public function testHasManyTokens()
    {
        //Creates a token
        $token = (new Token)->create('testToken', ['user_id' => 4]);

        $user = $this->Users->findById(4)->contain('Tokens')->first();

        $this->assertEquals(1, count($user->tokens));
        $this->assertInstanceOf('Tokens\Model\Entity\Token', $user->tokens[0]);
        $this->assertEquals(4, $user->tokens[0]->user_id);
        $this->assertEquals($token, $user->tokens[0]->token);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $query = $this->Users->find('active');
        $this->assertStringEndsWith('FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->active && !$entity->banned);
        }
    }

    /**
     * Test for `findBanned()` method
     * @test
     */
    public function testFindBanned()
    {
        $query = $this->Users->find('banned');
        $this->assertStringEndsWith('FROM users Users WHERE Users.banned = :c0', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue($entity->banned);
        }
    }

    /**
     * Test for `findPending()` method
     * @test
     */
    public function testFindPending()
    {
        $query = $this->Users->find('pending');
        $this->assertStringEndsWith('FROM users Users WHERE (Users.active = :c0 AND Users.banned = :c1)', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $entity) {
            $this->assertTrue(!$entity->active && !$entity->banned);
        }
    }

    /**
     * Test for `getActiveList()` method
     * @test
     */
    public function testGetActiveList()
    {
        $query = $this->Users->getActiveList();
        $this->assertContains('FROM users Users WHERE Users.active = :c0 ORDER BY username ASC', $query->sql());

        $list = $query->toArray();
        $this->assertEquals([
            4 => 'Abc Def',
            1 => 'Alfa Beta',
            3 => 'Ypsilon Zeta',
            5 => 'Mno Pqr',
        ], $list);

        $fromCache = Cache::read('active_users_list', $this->Users->cache)->toArray();
        $this->assertEquals($fromCache, $list);
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = [
            'username' => 'test',
            'group' => 1,
            'status' => 'active',
        ];

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2 AND Users.banned = :c3)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertTrue($query->getValueBinder()->bindings()[':c2']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c3']['value']);

        $data['status'] = 'pending';

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertStringEndsWith('FROM users Users WHERE (Users.username like :c0 AND Users.group_id = :c1 AND Users.active = :c2)', $query->sql());
        $this->assertEquals('%test%', $query->getValueBinder()->bindings()[':c0']['value']);
        $this->assertEquals(1, $query->getValueBinder()->bindings()[':c1']['value']);
        $this->assertFalse($query->getValueBinder()->bindings()[':c2']['value']);

        $data['status'] = 'banned';

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
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
        $data = ['status' => 'invalid', 'username' => 'ab'];

        $query = $this->Users->queryFromFilter($this->Users->find(), $data);
        $this->assertEmpty($query->getValueBinder()->bindings());
    }

    /**
     * Test for `validationDoNotRequirePresence()` method
     * @test
     */
    public function testValidationDoNotRequirePresence()
    {
        $example = ['email' => 'example@test.com'];

        $entity = $this->Users->newEntity($example);
        $this->assertNotEmpty($entity->getErrors());

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->getErrors());

        $example['email_repeat'] = $example['email'];

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
        $this->assertEmpty($entity->getErrors());

        $example['email_repeat'] = $example['email'] . 'aaa';

        $entity = $this->Users->newEntity($example, ['validate' => 'DoNotRequirePresence']);
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
        $this->example['password'] = $this->example['password_repeat'] = '';

        $expected = [
            'password' => ['_empty' => 'This field cannot be left empty'],
            'password_repeat' => ['_empty' => 'This field cannot be left empty'],
        ];

        $entity = $this->Users->newEntity($this->example);
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example);
        $this->assertEquals($expected, $entity->getErrors());

        $entity = $this->Users->patchEntity($this->Users->get(1), $this->example, ['validate' => 'EmptyPassword']);
        $this->assertEmpty($entity->getErrors());
    }
}
