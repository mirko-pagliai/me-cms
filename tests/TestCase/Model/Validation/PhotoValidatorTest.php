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
    }

    /**
     * Test validation for `album_id` property
     * @test
     */
    public function testValidationForAlbumId()
    {
        $entity = $this->Photos->newEntity([
            'album_id' => 1,
            'filename' => 'pic.jpg',
        ]);
        $this->assertEmpty($entity->errors());

        $entity = $this->Photos->newEntity(['filename' => 'pic.jpg']);
        $this->assertEquals(['album_id' => ['_required' => 'This field is required']], $entity->errors());

        $entity = $this->Photos->newEntity([
            'album_id' => 'string',
            'filename' => 'pic.jpg',
        ]);
        $this->assertEquals(['album_id' => ['naturalNumber' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $entity = $this->Photos->newEntity([
            'album_id' => 1,
            'filename' => 'pic.jpg',
        ]);
        $this->assertEmpty($entity->errors());

        $entity = $this->Photos->newEntity(['album_id' => 1]);
        $this->assertEquals(['filename' => ['_required' => 'This field is required']], $entity->errors());

        $expected = ['filename' => ['extension' => 'Valid extensions: gif, jpg, jpeg, png']];

        $entity = $this->Photos->newEntity([
            'album_id' => 1,
            'filename' => 'pic',
        ]);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Photos->newEntity([
            'album_id' => 1,
            'filename' => 'text.txt',
        ]);
        $this->assertEquals($expected, $entity->errors());
    }
}
