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
namespace MeCms\Test\TestCase\Controller\Admin;

use MeCms\Model\Entity\PhotosAlbum;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PhotosAlbumsControllerTest class
 */
class PhotosAlbumsControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.PhotosAlbums',
    ];

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

        //With `delete` action
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ], 'delete');
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'PhotosAlbums' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(PhotosAlbum::class, $this->viewVariable('albums'));
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
        $this->assertTemplate('Admin' . DS . 'PhotosAlbums' . DS . 'add.php');
        $this->assertInstanceof(PhotosAlbum::class, $this->viewVariable('album'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'new category', 'slug' => 'category-slug']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(PhotosAlbum::class, $this->viewVariable('album'));
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
        $this->assertTemplate('Admin' . DS . 'PhotosAlbums' . DS . 'edit.php');
        $this->assertInstanceof(PhotosAlbum::class, $this->viewVariable('album'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(PhotosAlbum::class, $this->viewVariable('album'));
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        //POST request. This album has no photos
        $this->post($this->url + ['action' => 'delete', 3]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(3)->isEmpty());

        //POST request. This album has some photos, so it cannot be deleted
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_BEFORE_DELETE);
        $this->assertFalse($this->Table->findById(1)->isEmpty());
    }
}
