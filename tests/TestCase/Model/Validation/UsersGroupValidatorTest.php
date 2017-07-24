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

        $this->UsersGroups = TableRegistry::get(ME_CMS . '.UsersGroups');
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->UsersGroups->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->UsersGroups->newEntity($copy)->getErrors());
        }
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
            ], $this->UsersGroups->newEntity($this->example)->getErrors());
        }

        foreach (['ab', str_repeat('a', 101)] as $value) {
            $this->example['name'] = $value;
            $this->assertEquals([
                'name' => ['lengthBetween' => 'Must be between 3 and 100 chars'],
            ], $this->UsersGroups->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['name'] = $value;
            $this->assertEmpty($this->UsersGroups->newEntity($this->example)->getErrors());
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
            ], $this->UsersGroups->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 100)] as $value) {
            $this->example['label'] = $value;
            $this->assertEmpty($this->UsersGroups->newEntity($this->example)->getErrors());
        }
    }
}
