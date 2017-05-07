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
 * TagValidatorTest class
 */
class TagValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

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
        'plugin.me_cms.tags',
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

        $this->Tags = TableRegistry::get('MeCms.Tags');

        $this->example = [
            'tag' => 'my tag',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->Tags->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `tag` property
     * @test
     */
    public function testValidationForTag()
    {
        foreach (['AbC', 'ab_c', 'ab-c', 'abc$'] as $value) {
            $this->example['tag'] = $value;
            $this->assertEquals([
                'tag' => ['validTagChars' => 'Allowed chars: lowercase letters, numbers, space'],
            ], $this->Tags->newEntity($this->example)->getErrors());
        }

        foreach (['ab', str_repeat('a', 31)] as $value) {
            $this->example['tag'] = $value;
            $this->assertEquals([
                'tag' => ['validTagLength' => 'Must be between 3 and 30 chars'],
            ], $this->Tags->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 30)] as $value) {
            $this->example['tag'] = $value;
            $this->assertEmpty($this->Tags->newEntity($this->example)->getErrors());
        }
    }
}
