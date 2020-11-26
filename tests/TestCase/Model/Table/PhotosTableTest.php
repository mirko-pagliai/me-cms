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

namespace MeCms\Test\TestCase\Model\Table;

use MeCms\Model\Validation\PhotoValidator;
use MeCms\TestSuite\TableTestCase;
use Tools\Filesystem;

/**
 * PhotosTableTest class
 */
class PhotosTableTest extends TableTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * @var array
     */
    protected static $example = [
        'album_id' => 1,
        'filename' => 'pic.jpg',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Photos',
        'plugin.MeCms.PhotosAlbums',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $file = PHOTOS . self::$example['album_id'] . DS . self::$example['filename'];
        @mkdir(dirname($file));
        @copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->unlinkRecursive(PHOTOS, 'empty', true);
    }

    /**
     * Test for event methods
     * @test
     */
    public function testEventMethods()
    {
        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));
        $this->assertEquals(['width' => 400, 'height' => 400], $entity->get('size'));
        $this->assertFileExists($entity->get('path'));
        $this->assertTrue($this->Table->delete($entity));
        $this->assertFileNotExists($entity->get('path'));
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->Table->newEntity(self::$example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity(self::$example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['filename' => ['_isUnique' => I18N_VALUE_ALREADY_USED]], $entity->getErrors());

        $entity = $this->Table->newEntity(['album_id' => 999, 'filename' => 'pic2.jpg']);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals(['album_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('photos', $this->Table->getTable());
        $this->assertEquals('filename', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertBelongsTo($this->Table->Albums);
        $this->assertEquals('album_id', $this->Table->Albums->getForeignKey());
        $this->assertEquals('INNER', $this->Table->Albums->getJoinType());
        $this->assertEquals('MeCms.PhotosAlbums', $this->Table->Albums->getClassName());

        $this->assertHasBehavior(['Timestamp', 'CounterCache']);

        $this->assertInstanceOf(PhotoValidator::class, $this->Table->getValidator());
    }

    /**
     * Test for `find()` methods
     * @test
     */
    public function testFindMethods()
    {
        $query = $this->Table->find('active');
        $this->assertStringEndsWith('FROM photos Photos WHERE Photos.active = :c0', $query->sql());
        $this->assertTrue($query->getValueBinder()->bindings()[':c0']['value']);

        $query = $this->Table->find('pending');
        $this->assertStringEndsWith('FROM photos Photos WHERE Photos.active = :c0', $query->sql());
        $this->assertFalse($query->getValueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $query = $this->Table->queryFromFilter($this->Table->find(), ['album' => 2]);
        $this->assertStringEndsWith('FROM photos Photos WHERE album_id = :c0', $query->sql());
        $this->assertEquals(2, $query->getValueBinder()->bindings()[':c0']['value']);
    }
}
