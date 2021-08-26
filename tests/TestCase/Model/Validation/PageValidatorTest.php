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
 * PageValidatorTest class
 */
class PageValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = [
        'category_id' => 1,
        'title' => 'My title',
        'slug' => 'my-slug',
        'text' => 'My text',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
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
}
