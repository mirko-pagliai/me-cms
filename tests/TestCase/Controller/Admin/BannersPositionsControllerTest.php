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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\BannersPositionsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * BannersPositionsControllerTest class
 */
class BannersPositionsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Model\Table\BannersPositionsTable
     */
    protected $BannersPositions;

    /**
     * @var \MeCms\Controller\Admin\BannersPositionsController
     */
    protected $Controller;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.banners_positions',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUserGroup('admin');

        $this->BannersPositions = TableRegistry::get(ME_CMS . '.BannersPositions');

        $this->Controller = new BannersPositionsController;

        Cache::clear(false, $this->BannersPositions->cache);

        $this->url = ['controller' => 'BannersPositions', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->BannersPositions, $this->Controller);
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/index.ctp');

        $positionsFromView = $this->viewVariable('positions');
        $this->assertInstanceof('Cake\ORM\ResultSet', $positionsFromView);
        $this->assertNotEmpty($positionsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/add.ctp');

        $positionFromView = $this->viewVariable('position');
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
        $this->assertNotEmpty($positionFromView);

        //POST request. Data are valid
        $this->post($url, [
            'title' => 'new-position-title',
            'descriptions' => 'new position description',
        ]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $positionFromView = $this->viewVariable('position');
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
        $this->assertNotEmpty($positionFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/BannersPositions/edit.ctp');

        $positionFromView = $this->viewVariable('position');
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
        $this->assertNotEmpty($positionFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another-title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $positionFromView = $this->viewVariable('position');
        $this->assertInstanceof('MeCms\Model\Entity\BannersPosition', $positionFromView);
        $this->assertNotEmpty($positionFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $id = $this->BannersPositions->find()->where(['banner_count <' => 1])->extract('id')->first();

        //POST request. This position has no banner
        $this->post(array_merge($this->url, ['action' => 'delete', $id]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $id = $this->BannersPositions->find()->where(['banner_count >=' => 1])->extract('id')->first();

        //POST request. This position has some banners, so it cannot be deleted
        $this->post(array_merge($this->url, ['action' => 'delete', $id]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession(
            'Before deleting this, you must delete or reassign all items that belong to this element',
            'Flash.flash.0.message'
        );
    }
}
