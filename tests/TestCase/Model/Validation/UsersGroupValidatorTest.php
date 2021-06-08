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
 * UsersGroupValidatorTest class
 */
class UsersGroupValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = ['name' => 'group', 'label' => 'Group label'];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test validation for `name` property
     * @test
     */
    public function testValidationForName(): void
    {
        foreach (['Abc', 'ab1', 'ab-c', 'ab$'] as $name) {
            $errors = $this->Table->newEntity(compact('name') + $this->example)->getErrors();
            $this->assertEquals(['name' => ['valid' => 'Allowed chars: lowercase letters']], $errors);
        }

        foreach (['ab', str_repeat('a', 101)] as $name) {
            $errors = $this->Table->newEntity(compact('name') + $this->example)->getErrors();
            $this->assertEquals(['name' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 100)] as $name) {
            $errors = $this->Table->newEntity(compact('name') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }

    /**
     * Test validation for `label` property
     * @test
     */
    public function testValidationForLabel(): void
    {
        foreach (['ab', str_repeat('a', 101)] as $label) {
            $errors = $this->Table->newEntity(compact('label') + $this->example)->getErrors();
            $this->assertEquals(['label' => ['lengthBetween' => 'Must be between 3 and 100 chars']], $errors);
        }

        foreach (['abc', str_repeat('a', 100)] as $label) {
            $errors = $this->Table->newEntity(compact('label') + $this->example)->getErrors();
            $this->assertEmpty($errors);
        }
    }
}
