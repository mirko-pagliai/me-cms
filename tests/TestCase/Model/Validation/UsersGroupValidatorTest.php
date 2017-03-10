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
namespace MeCms\Test\TestCase\Model\Validation;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * UsersGroupValidatorTest class
 */
class UsersGroupValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersGroupsTable
     */
    protected $UsersGroups;

    /**
     * Example data
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
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

        $this->example = [
            'name' => 'group',
            'label' => 'Group label',
        ];

        $this->UsersGroups = TableRegistry::get('MeCms.UsersGroups');
    }

    /**
     * Test validation for `name` property
     * @test
     */
    public function testValidationForName()
    {
        foreach (['Abc', 'ab1', 'ab-c', 'ab$'] as $value) {
            $this->example['name'] = $value;
            $this->assertEquals([
                'name' => ['valid' => 'Allowed chars: lowercase letters'],
            ], $this->UsersGroups->newEntity($this->example)->errors());
        }

        foreach (['ab', str_repeat('a', 101)] as $value) {
            $this->example['name'] = $value;
            $this->assertEquals([
                'name' => ['lengthBetween' => 'Must be between 3 and 100 chars'],
            ], $this->UsersGroups->newEntity($this->example)->errors());
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['name'] = $value;
            $this->assertEmpty($this->UsersGroups->newEntity($this->example)->errors());
        }
    }

    /**
     * Test validation for `label` property
     * @test
     */
    public function testValidationForLabel()
    {
        foreach (['ab', str_repeat('a', 101)] as $value) {
            $this->example['label'] = $value;
            $this->assertEquals([
                'label' => ['lengthBetween' => 'Must be between 3 and 100 chars'],
            ], $this->UsersGroups->newEntity($this->example)->errors());
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['label'] = $value;
            $this->assertEmpty($this->UsersGroups->newEntity($this->example)->errors());
        }
    }
}
