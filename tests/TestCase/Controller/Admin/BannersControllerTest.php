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
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;
use Cake\Routing\DispatcherFactory;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\BannersController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;
use MeTools\Controller\Component\UploaderComponent;

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
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.banners',
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
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        //Sets key for cookies
        $controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');

        //Mocks the `Uploader` component
        $controller->Uploader = $this->getMockBuilder(UploaderComponent::class)
            ->setConstructorArgs([new ComponentRegistry])
            ->getMock();

        $controller->Uploader->method('set')
            ->will($this->returnSelf());

        $controller->Uploader->method('mimetype')
            ->will($this->returnSelf());

        $controller->Uploader->method('save')
            ->will($this->returnValue('/full/path/to/file_to_upload.jpg'));

        parent::controllerSpy($event, $controller);
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
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Banners/index.ctp');

        $bannersFromView = $this->viewVariable('banners');
        $this->assertInstanceof('Cake\ORM\ResultSet', $bannersFromView);
        $this->assertNotEmpty($bannersFromView);

        foreach ($bannersFromView as $banner) {
            $this->assertInstanceof('MeCms\Model\Entity\Banner', $banner);
        }

        $this->assertCookie(null, 'renderBanners');
    }

    /**
     * Tests for `index()` method, render as `grid`
     * @test
     */
    public function testIndexAsGrid()
    {
        $this->get(array_merge($this->url, ['action' => 'index', '?' => ['render' => 'grid']]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Banners/index_as_grid.ctp');

        $this->assertCookie('grid', 'renderBanners');
    }

    /**
     * Tests for `index()` method, render as `grid` with cookie
     * @test
     */
    public function testIndexWithCookie()
    {
        $this->cookie('renderBanners', 'grid');

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Banners/index_as_grid.ctp');

        $this->assertCookie('grid', 'renderBanners');
    }

    /**
     * Tests for `upload()` method, with no position
     * @test
     */
    public function testUploadNoPosition()
    {
//        DispatcherFactory::clear();

        $this->post(array_merge($this->url, ['action' => 'upload']), ['file' => true]);
        $this->assertResponseFailure();
    }

    /**
     * Tests for `upload()` method
     * @test
     */
    public function testUpload()
    {
        $url = array_merge($this->url, ['action' => 'upload']);

        //GET request
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Banners/upload.ctp');

        //POST request. Tries to upload a banner
        $this->post(array_merge($url, ['?' => ['position' => 1]]), ['file' => true]);
        $this->assertResponseOk();

        //Checks the banner has been saved
        $banner = $this->Banners->find()->last();
        $this->assertEquals(1, $banner['position_id']);
        $this->assertEquals('file_to_upload.jpg', $banner['filename']);

        //Deletes all positions (except for the first one) and all banners
        $this->Banners->deleteAll(['id >=' => 1]);
        $this->Banners->Positions->deleteAll(['id >' => 1]);

        //POST request again. Tries to upload a banner
        //This should also work without the location in the query string, as
        //  there is only one location
        $this->post($url, ['file' => true]);
        $this->assertResponseOk();

        //Checks the banner has been saved
        $banner = $this->Banners->find()->last();
        $this->assertEquals(1, $banner['position_id']);
        $this->assertEquals('file_to_upload.jpg', $banner['filename']);
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
        $this->assertTemplate(ROOT . 'src/Template/Admin/Banners/edit.ctp');

        $bannerFromView = $this->viewVariable('banner');
        $this->assertInstanceof('MeCms\Model\Entity\Banner', $bannerFromView);
        $this->assertNotEmpty($bannerFromView);

        //POST request. Data are valid
        $this->post($url, ['description' => 'New description for first banner']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. Data are invalid
        $this->post($url, ['target' => 'invalidTarget']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $bannerFromView = $this->viewVariable('banner');
        $this->assertInstanceof('MeCms\Model\Entity\Banner', $bannerFromView);
        $this->assertNotEmpty($bannerFromView);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $this->get(array_merge($this->url, ['action' => 'download', 1]));
        $this->assertResponseOk();
        $this->assertFileResponse(BANNERS . 'banner1.jpg');
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post(array_merge($this->url, ['action' => 'delete', 1]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');
    }
}
