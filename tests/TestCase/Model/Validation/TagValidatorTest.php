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
 * TagValidatorTest class
 */
class TagValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = ['tag' => 'my tag'];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Tags',
    ];

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testAllDataAreRequired(): void
    {
        $this->assertEmpty($this->Table->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `tag` property
     * @test
     */
    public function testValidationForTag(): void
    {
        foreach (['AbC', 'ab_c', 'ab-c', 'abc$'] as $tag) {
            $errors = $this->Table->newEntity(compact('tag') + $this->example)->getErrors();
            $this->assertEquals(['tag' => [
                'validChars' => sprintf('%s: %s', I18N_ALLOWED_CHARS, I18N_LOWERCASE_NUMBERS_SPACE),
            ]], $errors);
        }

        foreach (['ab', str_repeat('a', 41)] as $tag) {
            $errors = $this->Table->newEntity(compact('tag') + $this->example)->getErrors();
            $this->assertEquals(['tag' => ['lengthBetween' => 'Must be between 3 and 40 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 40)] as $tag) {
            $errors = $this->Table->newEntity(compact('tag') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
