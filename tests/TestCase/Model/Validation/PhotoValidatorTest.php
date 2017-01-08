<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
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

        $this->Photos = TableRegistry::get('MeCms.Photos');

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
        $errors = $this->Photos->newEntity($this->example)->errors();
        $this->assertEmpty($errors);

        foreach ($this->example as $key => $value) {
            //Create a copy of the example data and removes the current value
            $copy = $this->example;
            unset($copy[$key]);

            $errors = $this->Photos->newEntity($copy)->errors();
            $this->assertEquals([$key => ['_required' => 'This field is required']], $errors);
        }
    }

    /**
     * Test validation for `album_id` property
     * @test
     */
    public function testValidationForAlbumId()
    {
        $this->example['album_id'] = 'string';
        $errors = $this->Photos->newEntity($this->example)->errors();
        $this->assertEquals(['album_id' => ['naturalNumber' => 'You have to select a valid option']], $errors);
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $expected = ['filename' => ['extension' => 'Valid extensions: gif, jpg, jpeg, png']];

        foreach (['pic', 'text.txt'] as $value) {
            $this->example['filename'] = $value;
            $errors = $this->Photos->newEntity($this->example)->errors();
            $this->assertEquals($expected, $errors);
        }
    }
}
