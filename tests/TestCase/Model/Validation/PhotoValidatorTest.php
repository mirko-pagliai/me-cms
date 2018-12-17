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
 * PhotoValidatorTest class
 */
class PhotoValidatorTest extends ValidationTestCase
{
    /**
     * @var array
     */
    protected $example = ['album_id' => 1, 'filename' => 'pic.jpg'];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Photos',
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
     * Test validation for `album_id` property
     * @test
     */
    public function testValidationForAlbumId()
    {
        $errors = $this->Table->newEntity(['album_id' => 'str'] + $this->example)->getErrors();
        $this->assertEquals(['album_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION]], $errors);
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
}
