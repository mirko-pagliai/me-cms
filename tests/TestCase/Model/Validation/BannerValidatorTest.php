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
 * BannerValidatorTest class
 */
class BannerValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = ['position_id' => 1, 'filename' => 'pic.jpg'];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Banners',
    ];

    /**
     * Test validation for `position_id` property
     * @test
     */
    public function testValidationForPositionId()
    {
        $errors = $this->Table->newEntity(['position_id' => 'string'] + $this->example)->getErrors();
        $this->assertEquals(['position_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        foreach (['pic', 'text.txt'] as $filename) {
            $errors = $this->Table->newEntity(compact('filename') + $this->example)->getErrors();
            $this->assertEquals(['filename' => ['extension' => 'Valid extensions: gif, jpg, jpeg, png']], $errors);
        }
    }

    /**
     * Test validation for `target` property
     * @test
     */
    public function testValidationForTarget()
    {
        $errors = $this->Table->newEntity(['target' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['target' => ['url' => 'Must be a valid url']], $errors);

        $errors = $this->Table->newEntity(['target' => 'http://example.com/' . str_repeat('a', 248)] + $this->example)->getErrors();
        $this->assertEquals(['target' => ['maxLength' => 'Must be at most 255 chars']], $errors);

        $errors = $this->Table->newEntity(['target' => 'http://example.com/' . str_repeat('a', 247)] + $this->example)->getErrors();
        $this->assertEmpty($this->Table->newEntity($this->example)->getErrors());

        $errors = $this->Table->newEntity(['target' => 'http://example.com'] + $this->example)->getErrors();
        $this->assertEmpty($errors);
    }

    /**
     * Test validation for `thumbnail` property
     * @test
     */
    public function testValidationForThumbnail()
    {
        $errors = $this->Table->newEntity(['thumbnail' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['thumbnail' => ['boolean' => I18N_SELECT_VALID_OPTION]], $errors);

        foreach ([true, false] as $thumbnail) {
            $this->assertEmpty($this->Table->newEntity(compact('thumbnail') + $this->example)->getErrors());
        }
    }
}
