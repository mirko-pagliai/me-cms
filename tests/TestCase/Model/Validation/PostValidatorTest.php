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
use MeCms\TestSuite\ValidationTestCase;

/**
 * PostValidatorTest class
 */
class PostValidatorTest extends ValidationTestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

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
        'plugin.me_cms.posts',
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

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        $this->example = [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'My title',
            'slug' => 'my-slug',
            'text' => 'My text',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertAllDataAreRequired($this->Posts, $this->example);
    }

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId()
    {
        $this->example['category_id'] = 'string';
        $this->assertEquals([
            'category_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION],
        ], $this->Posts->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `tags` property
     * @test
     */
    public function testValidationForTags()
    {
        foreach (['ab', str_repeat('a', 31)] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEquals([
                'tags' => ['validTagsLength' => 'Each tag must be between 3 and 30 chars'],
            ], $this->Posts->newEntity($this->example)->getErrors());
        }

        foreach (['Abc', 'ab$', 'ab-c', 'ab_c'] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEquals([
                'tags' => ['validTagsChars' => 'Allowed chars: lowercase letters, numbers, space'],
            ], $this->Posts->newEntity($this->example)->getErrors());
        }

        foreach (['abc', str_repeat('a', 30)] as $value) {
            $this->example['tags_as_string'] = $value;
            $this->assertEmpty($this->Posts->newEntity($this->example)->getErrors());
        }

        foreach ([
            'first, second',
            'first,  second',
            'first , second',
            'first ,second',
            'first  ,second',
            'first,second',
            ' first, second',
            'first, second ',
            ' first, second ',
            ' first , second ',
        ] as $value) {
            $this->assertEmpty($this->Posts->newEntity($this->example)->getErrors());
        }
    }
}
