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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Controller\Admin\PhotosAlbumsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PhotosAlbumsControllerTest class
 */
class PhotosAlbumsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\PhotosAlbumsController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PhotosAlbums
     */
    protected $PhotosAlbums;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.PhotosAlbums',
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

        $this->Controller = new PhotosAlbumsController;

        $this->PhotosAlbums = TableRegistry::get(ME_CMS . '.PhotosAlbums');

        Cache::clear(false, $this->PhotosAlbums->cache);

        $this->url = ['controller' => 'PhotosAlbums', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
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
            'user' => true,
        ]);

        //`delete` action
        $this->Controller = new PhotosAlbumsController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'delete');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PhotosAlbums/index.ctp');

        $albumsFromView = $this->viewVariable('albums');
        $this->assertNotEmpty($albumsFromView);
        $this->assertContainsInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PhotosAlbums/add.ctp');

        $albumFromView = $this->viewVariable('album');
        $this->assertNotEmpty($albumFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'new category', 'slug' => 'category-slug']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $albumFromView = $this->viewVariable('album');
        $this->assertNotEmpty($albumFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/PhotosAlbums/edit.ctp');

        $albumFromView = $this->viewVariable('album');
        $this->assertNotEmpty($albumFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $albumFromView = $this->viewVariable('album');
        $this->assertNotEmpty($albumFromView);
        $this->assertInstanceof('MeCms\Model\Entity\PhotosAlbum', $albumFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $id = $this->PhotosAlbums->find()->where(['photo_count <' => 1])->extract('id')->first();

        //POST request. This album has no photos
        $this->post($this->url + ['action' => 'delete', $id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        $id = $this->PhotosAlbums->find()->where(['photo_count >=' => 1])->extract('id')->first();

        //POST request. This album has some photos, so it cannot be deleted
        $this->post($this->url + ['action' => 'delete', $id]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
    }
}
