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
 * BannerValidatorTest class
 */
class BannerValidatorTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\BannersTable
     */
    protected $Banners;

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

        $this->Banners = TableRegistry::get('MeCms.Banners');
    }

    /**
     * Test validation for `filename` property
     * @test
     */
    public function testValidationForFilename()
    {
        $entity = $this->Banners->newEntity([
            'filename' => 'pic.jpg',
            'position_id' => 1,
        ]);
        $this->assertEmpty($entity->errors());

        $entity = $this->Banners->newEntity(['position_id' => 1]);
        $this->assertEquals(['filename' => ['_required' => 'This field is required']], $entity->errors());

        $expected = ['filename' => ['extension' => 'Valid extensions: gif, jpg, jpeg, png']];

        $entity = $this->Banners->newEntity([
            'filename' => 'pic',
            'position_id' => 1,
        ]);
        $this->assertEquals($expected, $entity->errors());

        $entity = $this->Banners->newEntity([
            'filename' => 'text.txt',
            'position_id' => 1,
        ]);
        $this->assertEquals($expected, $entity->errors());
    }

    /**
     * Test validation for `position_id` property
     * @test
     */
    public function testValidationForPositionId()
    {
        $entity = $this->Banners->newEntity([
            'filename' => 'pic.jpg',
            'position_id' => 1,
        ]);
        $this->assertEmpty($entity->errors());

        $entity = $this->Banners->newEntity(['filename' => 'pic.jpg']);
        $this->assertEquals(['position_id' => ['_required' => 'This field is required']], $entity->errors());

        $entity = $this->Banners->newEntity([
            'filename' => 'pic.jpg',
            'position_id' => 'string',
        ]);
        $this->assertEquals(['position_id' => ['naturalNumber' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test validation for `target` property
     * @test
     */
    public function testValidationForTarget()
    {
        $data = [
            'filename' => 'pic.jpg',
            'position_id' => 1,
            'target' => 'http://example.com',
        ];

        $entity = $this->Banners->newEntity($data);
        $this->assertEmpty($entity->errors());

        $data['target'] = 'string';
        $entity = $this->Banners->newEntity($data);
        $this->assertEquals(['target' => ['url' => 'Must be a valid url']], $entity->errors());

        $data['target'] = 'http://example.com/' . str_repeat('a', 237);
        $entity = $this->Banners->newEntity($data);
        $this->assertEquals(['target' => ['maxLength' => 'Must be at most 255 chars']], $entity->errors());

        $data['target'] = 'http://example.com/' . str_repeat('a', 236);
        $entity = $this->Banners->newEntity($data);
        $this->assertEmpty($entity->errors());
    }

    /**
     * Test validation for `thumbnail` property
     * @test
     */
    public function testValidationForThumbnail()
    {
        $data = [
            'filename' => 'pic.jpg',
            'position_id' => 1,
            'thumbnail' => true,
        ];

        $entity = $this->Banners->newEntity($data);
        $this->assertEmpty($entity->errors());

        $data['thumbnail'] = false;
        $entity = $this->Banners->newEntity($data);
        $this->assertEmpty($entity->errors());

        $data['thumbnail'] = 'string';
        $entity = $this->Banners->newEntity($data);
        $this->assertEquals(['thumbnail' => ['boolean' => 'You have to select a valid option']], $entity->errors());
    }
}
