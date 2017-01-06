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
 * PageValidatorTest class
 */
class PageValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
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

        $this->Pages = TableRegistry::get('MeCms.Pages');
    }

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId()
    {
        $data = [
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
            'category_id' => 1,
        ];

        $entity = $this->Pages->newEntity($data);
        $this->assertEmpty($entity->errors());

        $data['category_id'] = 'string';
        $entity = $this->Pages->newEntity($data);
        $this->assertEquals(['category_id' => ['naturalNumber' => 'You have to select a valid option']], $entity->errors());

        unset($data['category_id']);
        $entity = $this->Pages->newEntity($data);
        $this->assertEquals(['category_id' => ['_required' => 'This field is required']], $entity->errors());
    }

    /**
     * Test validation for `title` property
     * @test
     */
    public function testValidationForTitle()
    {
        $data = [
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
            'category_id' => 1,
        ];

        $entity = $this->Pages->newEntity($data);
        $this->assertEmpty($entity->errors());

        unset($data['title']);
        $entity = $this->Pages->newEntity($data);
        $this->assertEquals(['title' => ['_required' => 'This field is required']], $entity->errors());
    }

    /**
     * Test validation for `slug` property
     * @test
     */
    public function testValidationForSlug()
    {
        $data = [
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
            'category_id' => 1,
        ];

        $entity = $this->Pages->newEntity($data);
        $this->assertEmpty($entity->errors());

        unset($data['slug']);
        $entity = $this->Pages->newEntity($data);
        $this->assertEquals(['slug' => ['_required' => 'This field is required']], $entity->errors());
    }

    /**
     * Test validation for `text` property
     * @test
     */
    public function testValidationForText()
    {
        $data = [
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
            'category_id' => 1,
        ];

        $entity = $this->Pages->newEntity($data);
        $this->assertEmpty($entity->errors());

        unset($data['text']);
        $entity = $this->Pages->newEntity($data);
        $this->assertEquals(['text' => ['_required' => 'This field is required']], $entity->errors());
    }
}