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
use MeCms\Controller\Admin\BannersController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * BannersControllerTest class
 */
class BannersControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Model\Table\BannersTable
     */
    protected $Banners;

    /**
     * @var \MeCms\Controller\Admin\BannersController
     */
    protected $Controller;

    /**
     * @var array
     */
    protected $url;

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

        $this->setUserGroup('admin');

        $this->Banners = TableRegistry::get('MeCms.Banners');

        $this->Controller = new BannersController;

        Cache::clear(false, $this->Banners->cache);

        $this->url = ['controller' => 'Banners', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Banners, $this->Controller);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('positions'));
    }

    /**
     * Tests for `beforeFilter()` method, with no positions
     * @test
     */
    public function testBeforeFilterNoPositions()
    {
        //Deletes all positions
        $this->Banners->Positions->deleteAll(['id IS NOT' => null]);

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertRedirect(['controller' => 'BannersPositions', 'action' => 'index']);
        $this->assertSession('You must first create a banner position', 'Flash.flash.0.message');
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //`delete` action
        $this->Controller = new BannersController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $url = array_merge($this->url, ['action' => 'download', 1]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertFileResponse(BANNERS . 'banner1.jpg');
    }
}
