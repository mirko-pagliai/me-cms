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
 * BannerValidatorTest class
 */
class BannerValidatorTest extends ValidationTestCase
{
    /**
     * @var \MeCms\Model\Table\BannersTable
     */
    protected $Banners;

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
        'plugin.me_cms.banners',
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

        $this->Banners = TableRegistry::get(ME_CMS . '.Banners');

        $this->example = ['position_id' => 1, 'filename' => 'pic.jpg'];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertAllDataAreRequired($this->Banners, $this->example);
    }

    /**
     * Test validation for `position_id` property
     * @test
     */
    public function testValidationForPositionId()
    {
        $this->example['position_id'] = 'string';
        $this->assertEquals([
            'position_id' => ['naturalNumber' => I18N_SELECT_VALID_OPTION],
        ], $this->Banners->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        foreach (['pic', 'text.txt'] as $value) {
            $this->example['filename'] = $value;
            $this->assertEquals([
                'filename' => ['extension' => 'Valid extensions: gif, jpg, jpeg, png'],
            ], $this->Banners->newEntity($this->example)->getErrors());
        }
    }

    /**
     * Test validation for `target` property
     * @test
     */
    public function testValidationForTarget()
    {
        $this->example['target'] = 'string';
        $this->assertEquals([
            'target' => ['url' => 'Must be a valid url'],
        ], $this->Banners->newEntity($this->example)->getErrors());

        $this->example['target'] = 'http://example.com/' . str_repeat('a', 237);
        $this->assertEquals([
            'target' => ['maxLength' => 'Must be at most 255 chars'],
        ], $this->Banners->newEntity($this->example)->getErrors());

        $this->example['target'] = 'http://example.com/' . str_repeat('a', 236);
        $this->assertEmpty($this->Banners->newEntity($this->example)->getErrors());

        $this->example['target'] = 'http://example.com';
        $this->assertEmpty($this->Banners->newEntity($this->example)->getErrors());
    }

    /**
     * Test validation for `thumbnail` property
     * @test
     */
    public function testValidationForThumbnail()
    {
        $this->example['thumbnail'] = 'string';
        $this->assertEquals([
            'thumbnail' => ['boolean' => I18N_SELECT_VALID_OPTION],
        ], $this->Banners->newEntity($this->example)->getErrors());

        foreach ([true, false] as $value) {
            $this->example['thumbnail'] = $value;
            $this->assertEmpty($this->Banners->newEntity($this->example)->getErrors());
        }
    }
}
