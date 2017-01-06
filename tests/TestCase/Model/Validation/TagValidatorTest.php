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
    }

    /**
     * Test validation for `tag` property
     * @test
     */
    public function testValidationForTag()
    {
        $entity = $this->Tags->newEntity([]);
        $this->assertEmpty($entity->errors());

        $entity = $this->Tags->newEntity(['tag' => 'abc']);
        $this->assertEmpty($entity->errors());

        $entity = $this->Tags->newEntity(['tag' => str_repeat('a', 30)]);
        $this->assertEmpty($entity->errors());

        $expected = ['tag' => ['lengthBetween' => 'Must be between 3 and 30 chars']];

        $entity = $this->Tags->newEntity(['tag' => 'ab']);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Tags->newEntity(['tag' => str_repeat('a', 31)]);
        $this->assertEquals($expected, $entity->errors());

        $expected = ['tag' => ['validTag' => 'Allowed chars: lowercase letters, numbers, space']];

        $entity = $this->Tags->newEntity(['tag' => 'AbC']);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Tags->newEntity(['tag' => 'ab_c']);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Tags->newEntity(['tag' => 'ab-c']);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Tags->newEntity(['tag' => 'abc$']);
        $this->assertEquals($expected, $entity->errors());
    }
}
