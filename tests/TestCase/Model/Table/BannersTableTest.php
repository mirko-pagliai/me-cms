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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * BannersTableTest class
 */
class BannersTableTest extends TestCase
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
        'plugin.me_cms.banners_positions',
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

        Cache::clear(false, $this->Banners->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Banners);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('banners', $this->Banners->cache);
    }

    /**
     * Test for `afterDelete()` method
     * @test
     */
    public function testAfterDelete()
    {
        $banner = $this->Banners->get(1);

        //Creates the file
        file_put_contents($banner->path, null);

        $this->assertFileExists($banner->path);

        //Deletes the banner
        $this->assertTrue($this->Banners->delete($banner));
        $this->assertFileNotExists($banner->path);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = [
            'position_id' => 1,
            'filename' => 'pic.jpg',
        ];

        $entity = $this->Banners->newEntity($example);
        $this->assertNotEmpty($this->Banners->save($entity));

        //Saves again the same entity
        $entity = $this->Banners->newEntity($example);
        $this->assertFalse($this->Banners->save($entity));
        $this->assertEquals(['filename' => ['_isUnique' => 'This value is already used']], $entity->errors());

        $entity = $this->Banners->newEntity([
            'position_id' => 999,
            'filename' => 'pic2.jpg',
        ]);
        $this->assertFalse($this->Banners->save($entity));
        $this->assertEquals(['position_id' => ['_existsIn' => 'You have to select a valid option']], $entity->errors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('banners', $this->Banners->table());
        $this->assertEquals('filename', $this->Banners->displayField());
        $this->assertEquals('id', $this->Banners->primaryKey());

        $this->assertEquals('Cake\ORM\Association\BelongsTo', get_class($this->Banners->Positions));
        $this->assertEquals('position_id', $this->Banners->Positions->foreignKey());
        $this->assertEquals('INNER', $this->Banners->Positions->joinType());
        $this->assertEquals('MeCms.BannersPositions', $this->Banners->Positions->className());

        $this->assertTrue($this->Banners->hasBehavior('Timestamp'));
        $this->assertTrue($this->Banners->hasBehavior('CounterCache'));
    }

    /**
     * Test for the `belongsTo` association with `BannersPositions`
     * @test
     */
    public function testBelongsToBannersPositions()
    {
        $banner = $this->Banners->findById(2)->contain(['Positions'])->first();

        $this->assertNotEmpty($banner->position);

        $this->assertEquals('MeCms\Model\Entity\BannersPosition', get_class($banner->position));
        $this->assertEquals(1, $banner->position->id);
    }

    /**
     * Test for `findActive()` method
     * @test
     */
    public function testFindActive()
    {
        $this->assertTrue($this->Banners->hasFinder('active'));

        $query = $this->Banners->find('active');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Banners.id AS `Banners__id`, Banners.position_id AS `Banners__position_id`, Banners.filename AS `Banners__filename`, Banners.target AS `Banners__target`, Banners.description AS `Banners__description`, Banners.active AS `Banners__active`, Banners.click_count AS `Banners__click_count`, Banners.created AS `Banners__created`, Banners.modified AS `Banners__modified` FROM banners Banners WHERE Banners.active = :c0', $query->sql());

        $this->assertTrue($query->valueBinder()->bindings()[':c0']['value']);

        $this->assertNotEmpty($query->count());

        foreach ($query->toArray() as $banner) {
            $this->assertTrue($banner->active);
        }
    }

    /**
     * Test for `queryFromFilter()` method
     * @test
     */
    public function testQueryFromFilter()
    {
        $data = ['position' => 2];

        $query = $this->Banners->queryFromFilter($this->Banners->find(), $data);
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals('SELECT Banners.id AS `Banners__id`, Banners.position_id AS `Banners__position_id`, Banners.filename AS `Banners__filename`, Banners.target AS `Banners__target`, Banners.description AS `Banners__description`, Banners.active AS `Banners__active`, Banners.click_count AS `Banners__click_count`, Banners.created AS `Banners__created`, Banners.modified AS `Banners__modified` FROM banners Banners WHERE Banners.position_id = :c0', $query->sql());

        $this->assertEquals(2, $query->valueBinder()->bindings()[':c0']['value']);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\BannerValidator',
            get_class($this->Banners->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
