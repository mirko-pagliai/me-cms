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

use MeCms\TestSuite\ValidationTestCase;

/**
 * PostValidatorTest class
 */
class PostValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = [
        'category_id' => 1,
        'user_id' => 1,
        'title' => 'My title',
        'slug' => 'my-slug',
        'text' => 'My text',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Posts',
        'plugin.me_cms.Tags',
    ];

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertAllDataAreRequired($this->example);
    }

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId()
    {
        $errors = $this->Table->newEntity(['category_id' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['category_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `tags` property
     * @test
     */
    public function testValidationForTags()
    {
        foreach (['ab', str_repeat('a', 31)] as $tags_as_string) {
            $errors = $this->Table->newEntity(compact('tags_as_string') + $this->example)->getErrors();
            $this->assertEquals(['tags' => ['validTagsLength' => 'Each tag must be between 3 and 30 chars']], $errors);
        }

        foreach (['Abc', 'ab$', 'ab-c', 'ab_c'] as $tags_as_string) {
            $errors = $this->Table->newEntity(compact('tags_as_string') + $this->example)->getErrors();
            $this->assertEquals(['tags' => ['validTagsChars' => 'Allowed chars: lowercase letters, numbers, space']], $errors);
        }

        foreach (['abc', str_repeat('a', 30)] as $tags_as_string) {
            $errors = $this->Table->newEntity(compact('tags_as_string') + $this->example)->getErrors();
            $this->assertEmpty($errors);
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
        ] as $tags_as_string) {
            $this->Table->newEntity(compact('tags_as_string') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
