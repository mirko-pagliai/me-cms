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
namespace MeCms\Test\TestCase\View\Helper;

use Cake\View\View;
use MeCms\View\Helper\MenuHelper;
use MeTools\TestSuite\TestCase;

/**
 * MenuHelperTest class
 */
class MenuHelperTest extends TestCase
{
    /**
     * @var \MeCms\View\Helper\MenuHelper
     */
    protected $Menu;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Menu = new MenuHelper(new View);
    }

    /**
     * Tests for `posts()` method
     * @test
     */
    public function testPosts()
    {
        list($menu, $title, $options) = $this->Menu->posts();

        $this->assertEquals([
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ], $menu);
        $this->assertEquals('Posts', $title);
        $this->assertEquals(['icon' => 'file-text-o'], $options);

        $expected = [
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/posts-categories/add" title="Add category">Add category</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ];

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu) = $this->Menu->posts();

        $this->assertEquals($expected, $menu);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->posts();

        $this->assertEquals($expected, $menu);
    }

    /**
     * Tests for `pages()` method
     * @test
     */
    public function testPages()
    {
        list($menu, $title, $options) = $this->Menu->pages();

        $this->assertEquals([
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
        ], $menu);
        $this->assertEquals('Pages', $title);
        $this->assertEquals(['icon' => 'files-o'], $options);

        $expected = [
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/add" title="Add page">Add page</a>',
            '<a href="/me-cms/admin/pages-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/pages-categories/add" title="Add category">Add category</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
        ];

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu) = $this->Menu->pages();

        $this->assertEquals($expected, $menu);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->pages();

        $this->assertEquals($expected, $menu);
    }

    /**
     * Tests for `photos()` method
     * @test
     */
    public function testPhotos()
    {
        list($menu, $title, $options) = $this->Menu->photos();

        $expected = [
            '<a href="/me-cms/admin/photos" title="List photos">List photos</a>',
            '<a href="/me-cms/admin/photos/upload" title="Upload photos">Upload photos</a>',
            '<a href="/me-cms/admin/photos-albums" title="List albums">List albums</a>',
            '<a href="/me-cms/admin/photos-albums/add" title="Add album">Add album</a>',
        ];

        $this->assertEquals($expected, $menu);
        $this->assertEquals('Photos', $title);
        $this->assertEquals(['icon' => 'camera-retro'], $options);

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu) = $this->Menu->photos();

        $this->assertEquals($expected, $menu);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->photos();

        $this->assertEquals($expected, $menu);
    }

    /**
     * Tests for `banners()` method
     * @test
     */
    public function testBanners()
    {
        $this->assertNull($this->Menu->banners());

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu, $title, $options) = $this->Menu->banners();

        $this->assertEquals([
            '<a href="/me-cms/admin/banners" title="List banners">List banners</a>',
            '<a href="/me-cms/admin/banners/upload" title="Upload banners">Upload banners</a>',
        ], $menu);
        $this->assertEquals('Banners', $title);
        $this->assertEquals(['icon' => 'shopping-cart'], $options);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->banners();

        $this->assertEquals([
            '<a href="/me-cms/admin/banners" title="List banners">List banners</a>',
            '<a href="/me-cms/admin/banners/upload" title="Upload banners">Upload banners</a>',
            '<a href="/me-cms/admin/banners-positions" title="List positions">List positions</a>',
            '<a href="/me-cms/admin/banners-positions/add" title="Add position">Add position</a>',
        ], $menu);
    }

    /**
     * Tests for `users()` method
     * @test
     */
    public function testUsers()
    {
        $this->assertNull($this->Menu->users());

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu, $title, $options) = $this->Menu->users();

        $this->assertEquals([
            '<a href="/me-cms/admin/users" title="List users">List users</a>',
            '<a href="/me-cms/admin/users/add" title="Add user">Add user</a>',
        ], $menu);
        $this->assertEquals('Users', $title);
        $this->assertEquals(['icon' => 'users'], $options);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->users();

        $this->assertEquals([
            '<a href="/me-cms/admin/users" title="List users">List users</a>',
            '<a href="/me-cms/admin/users/add" title="Add user">Add user</a>',
            '<a href="/me-cms/admin/users-groups" title="List groups">List groups</a>',
            '<a href="/me-cms/admin/users-groups/add" title="Add group">Add group</a>',
        ], $menu);
    }

    /**
     * Tests for `backups()` method
     * @test
     */
    public function testBackups()
    {
        $this->assertNull($this->Menu->backups());

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        $this->assertNull($this->Menu->backups());

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu, $title, $options) = $this->Menu->backups();

        $this->assertEquals([
            '<a href="/me-cms/admin/backups" title="List backups">List backups</a>',
            '<a href="/me-cms/admin/backups/add" title="Add backup">Add backup</a>',
        ], $menu);
        $this->assertEquals('Backups', $title);
        $this->assertEquals(['icon' => 'database'], $options);
    }

    /**
     * Tests for `systems()` method
     * @test
     */
    public function testSystems()
    {
        $this->assertNull($this->Menu->systems());

        //Menu for manager users
        $this->Menu->Auth->initialize(['group' => ['name' => 'manager']]);
        list($menu, $title, $options) = $this->Menu->systems();

        $this->assertEquals([
            '<a href="/me-cms/admin/systems/tmp-viewer" title="Temporary files">Temporary files</a>',
            '<a href="/me-cms/admin/systems/checkup" title="System checkup">System checkup</a>',
            '<a href="/me-cms/admin/systems/browser" title="Media browser">Media browser</a>',
            '<a href="/me-cms/admin/systems/changelogs" title="Changelogs">Changelogs</a>',
        ], $menu);
        $this->assertEquals('System', $title);
        $this->assertEquals(['icon' => 'wrench'], $options);

        //Menu for admin users
        $this->Menu->Auth->initialize(['group' => ['name' => 'admin']]);
        list($menu) = $this->Menu->systems();

        $this->assertEquals([
            '<a href="/me-cms/admin/systems/tmp-viewer" title="Temporary files">Temporary files</a>',
            '<a href="/me-cms/admin/logs" title="Log management">Log management</a>',
            '<a href="/me-cms/admin/systems/checkup" title="System checkup">System checkup</a>',
            '<a href="/me-cms/admin/systems/browser" title="Media browser">Media browser</a>',
            '<a href="/me-cms/admin/systems/changelogs" title="Changelogs">Changelogs</a>',
        ], $menu);
    }
}
