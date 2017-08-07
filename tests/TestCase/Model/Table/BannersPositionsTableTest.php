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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

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

        $this->BannersPositions = TableRegistry::get(ME_CMS . '.BannersPositions');

        Cache::clear(false, $this->BannersPositions->cache);
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
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $example = ['title' => 'my-title'];

        $entity = $this->BannersPositions->newEntity($example);
        $this->assertNotEmpty($this->BannersPositions->save($entity));

        //Tries to save again the same entity
        $entity = $this->BannersPositions->newEntity($example);
        $this->assertFalse($this->BannersPositions->save($entity));
        $this->assertEquals(['title' => ['_isUnique' => I18N_VALUE_ALREADY_USED]], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('banners_positions', $this->BannersPositions->getTable());
        $this->assertEquals('title', $this->BannersPositions->getDisplayField());
        $this->assertEquals('id', $this->BannersPositions->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->BannersPositions->Banners);
        $this->assertEquals('position_id', $this->BannersPositions->Banners->getForeignKey());
        $this->assertEquals(ME_CMS . '.Banners', $this->BannersPositions->Banners->className());

        $this->assertTrue($this->BannersPositions->hasBehavior('Timestamp'));

        $this->assertInstanceOf('MeCms\Model\Validation\BannersPositionValidator', $this->BannersPositions->validator());
    }

    /**
     * Test for the `hasMany` association with `Banners`
     * @test
     */
    public function testHasManyBanners()
    {
        $position = $this->BannersPositions->findById(1)->contain(['Banners'])->first();
        $this->assertNotEmpty($position->banners);

        foreach ($position->banners as $banner) {
            $this->assertInstanceOf('MeCms\Model\Entity\Banner', $banner);
            $this->assertEquals(1, $banner->position_id);
        }
    }
}
