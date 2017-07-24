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
use Cake\TestSuite\TestCase;

/**
 * PhotoValidatorTest class
 */
class PhotoValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable
     */
    protected $Photos;

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
        'plugin.me_cms.photos',
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

        $this->Photos = TableRegistry::get(ME_CMS . '.Photos');

        $this->example = [
            'album_id' => 1,
            'filename' => 'pic.jpg',
        ];
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @test
     */
    public function testValidationExampleData()
    {
        $this->assertEmpty($this->Photos->newEntity($this->example)->getErrors());

        foreach (array_keys($this->example) as $key) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $this->assertEquals([
                $key => ['_required' => 'This field is required'],
            ], $this->Photos->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation for `album_id` property
     * @test
     */
    public function testValidationForAlbumId()
    {
        $this->example['album_id'] = 'string';
        $this->assertEquals([
            'album_id' => ['naturalNumber' => 'You have to select a valid option'],
        ], $this->Photos->newEntity($this->example)->getErrors());
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
            ], $this->Photos->newEntity($this->example)->getErrors());
        }
    }
}
