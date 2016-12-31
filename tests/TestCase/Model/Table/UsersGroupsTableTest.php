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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

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

        $this->UsersGroups = TableRegistry::get('MeCms.UsersGroups');

        Cache::clear(false, $this->UsersGroups->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->UsersGroups);
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
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('users_groups', $this->UsersGroups->table());
        $this->assertEquals('label', $this->UsersGroups->displayField());
        $this->assertEquals('id', $this->UsersGroups->primaryKey());

        $this->assertEquals('Cake\ORM\Association\HasMany', get_class($this->UsersGroups->Users));
        $this->assertEquals('group_id', $this->UsersGroups->Users->foreignKey());
        $this->assertEquals('MeCms.Users', $this->UsersGroups->Users->className());

        $this->assertTrue($this->UsersGroups->hasBehavior('Timestamp'));
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
            $this->assertEquals('MeCms\Model\Entity\User', get_class($user));
            $this->assertEquals(3, $user->group_id);
        }
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\UsersGroupValidator',
            get_class($this->UsersGroups->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
