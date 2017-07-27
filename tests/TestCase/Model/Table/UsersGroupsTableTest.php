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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

/**
 * UsersGroupsTableTest class
 */
class UsersGroupsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersGroupsTable
     */
    protected $UsersGroups;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
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

        $this->UsersGroups = TableRegistry::get(ME_CMS . '.UsersGroups');

        Cache::clear(false, $this->UsersGroups->cache);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('users', $this->UsersGroups->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = ['name' => 'group', 'label' => 'Group label'];

        $entity = $this->UsersGroups->newEntity($example);
        $this->assertNotEmpty($this->UsersGroups->save($entity));

        //Saves again the same entity
        $entity = $this->UsersGroups->newEntity($example);
        $this->assertFalse($this->UsersGroups->save($entity));
        $this->assertEquals([
            'name' => ['_isUnique' => 'This value is already used'],
            'label' => ['_isUnique' => 'This value is already used'],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('users_groups', $this->UsersGroups->getTable());
        $this->assertEquals('label', $this->UsersGroups->getDisplayField());
        $this->assertEquals('id', $this->UsersGroups->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->UsersGroups->Users);
        $this->assertEquals('group_id', $this->UsersGroups->Users->getForeignKey());
        $this->assertEquals(ME_CMS . '.Users', $this->UsersGroups->Users->className());

        $this->assertTrue($this->UsersGroups->hasBehavior('Timestamp'));

        $this->assertInstanceOf('MeCms\Model\Validation\UsersGroupValidator', $this->UsersGroups->validator());
    }

    /**
     * Test for the `hasMany` association with `Users`
     * @test
     */
    public function testHasManyUsers()
    {
        $group = $this->UsersGroups->findById(3)->contain(['Users'])->first();

        $this->assertNotEmpty($group->users);

        foreach ($group->users as $user) {
            $this->assertInstanceOf('MeCms\Model\Entity\User', $user);
            $this->assertEquals(3, $user->group_id);
        }
    }
}
