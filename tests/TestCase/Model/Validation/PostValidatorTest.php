<?php
declare(strict_types=1);

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
        'plugin.MeCms.Posts',
        'plugin.MeCms.Tags',
    ];

    /**
     * Test validation for `category_id` property
     * @test
     */
    public function testValidationForCategoryId(): void
    {
        $errors = $this->Table->newEntity(['category_id' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['category_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `tags` property
     * @test
     */
    public function testValidationForTags(): void
    {
        foreach (['ab', str_repeat('a', 41)] as $tag) {
            $errors = $this->Table->newEntity(['tags_as_string' => $tag] + $this->example)->getErrors();
            $this->assertEquals(['tags' => [
                'validTags' => sprintf('Tag "%s": must be between 3 and 40 chars', $tag),
            ]], $errors);
        }

        foreach (['Abc', 'ab$', 'ab-c', 'ab_c'] as $tag) {
            $errors = $this->Table->newEntity(['tags_as_string' => $tag] + $this->example)->getErrors();
            $this->assertEquals(['tags' => [
                'validTags' => sprintf('Tag "%s": %s: %s', $tag, lcfirst(I18N_ALLOWED_CHARS), I18N_LOWERCASE_NUMBERS_SPACE),
            ]], $errors);
        }

        //Multiple errors
        $tags = ['ab$', str_repeat('a', 41)];
        $errors = $this->Table->newEntity(['tags_as_string' => implode(', ', $tags)] + $this->example)->getErrors();
        $errorsAsArray = explode(PHP_EOL, $errors['tags']['validTags']);
        $this->assertEquals([
            sprintf('Tag "%s": %s: %s', $tags[0], lcfirst(I18N_ALLOWED_CHARS), I18N_LOWERCASE_NUMBERS_SPACE),
            sprintf('Tag "%s": must be between 3 and 40 chars', $tags[1]),
        ], $errorsAsArray);

        foreach ([
            'abc',
            str_repeat('a', 40),
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
            $errors = $this->Table->newEntity(compact('tags_as_string') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
