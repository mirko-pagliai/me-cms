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
 * PostsCategoryValidatorTest class
 */
class PostsCategoryValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsCategoriesTable
     */
    protected $PostsCategories;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts_categories',
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

        $this->PostsCategories = TableRegistry::get('MeCms.PostsCategories');
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
        ];

        $entity = $this->PostsCategories->newEntity($data);
        $this->assertEmpty($entity->errors());

        unset($data['title']);
        $entity = $this->PostsCategories->newEntity($data);
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
        ];

        $entity = $this->PostsCategories->newEntity($data);
        $this->assertEmpty($entity->errors());

        unset($data['slug']);
        $entity = $this->PostsCategories->newEntity($data);
        $this->assertEquals(['slug' => ['_required' => 'This field is required']], $entity->errors());
    }
}
