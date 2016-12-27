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
 * BannersPositionsTableTest class
 */
class BannersPositionsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\BannersPositionsTable
     */
    protected $BannersPositions;

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

        $this->BannersPositions = TableRegistry::get('MeCms.BannersPositions');

        Cache::clear(false, $this->BannersPositions->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->BannersPositions);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('banners', $this->BannersPositions->cache);
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('banners_positions', $this->BannersPositions->table());
        $this->assertEquals('title', $this->BannersPositions->displayField());
        $this->assertEquals('id', $this->BannersPositions->primaryKey());

        $this->assertEquals('Cake\ORM\Association\HasMany', get_class($this->BannersPositions->Banners));
        $this->assertEquals('position_id', $this->BannersPositions->Banners->foreignKey());
        $this->assertEquals('MeCms.Banners', $this->BannersPositions->Banners->className());

        $this->assertTrue($this->BannersPositions->hasBehavior('Timestamp'));
    }

    /**
     * Test for the `hasMany` association with `Banners`
     * @test
     */
    public function testHasManyBanners()
    {
        $positions = $this->BannersPositions->findById(1)->contain(['Banners'])->first();

        $this->assertNotEmpty($positions->banners);

        foreach ($positions->banners as $banner) {
            $this->assertEquals('MeCms\Model\Entity\Banner', get_class($banner));
            $this->assertEquals(1, $banner->position_id);
        }
    }

    /**
     * Test for `getList()` method
     * @test
     */
    public function testGetList()
    {
        $positions = $this->BannersPositions->getList();
        $this->assertEquals([
            2 => 'left',
            1 => 'top',
        ], $positions);
    }

    /**
     * Test for `validationDefault()` method
     * @test
     */
    public function testValidationDefault()
    {
        $this->assertEquals(
            'MeCms\Model\Validation\BannersPositionValidator',
            get_class($this->BannersPositions->validationDefault(new \Cake\Validation\Validator))
        );
    }
}
