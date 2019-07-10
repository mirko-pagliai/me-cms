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
namespace MeCms\Test\TestCase\View\Helper;

use MeTools\TestSuite\HelperTestCase;
use MeTools\View\Helper\HtmlHelper;

/**
 * MenuHelperTest class
 */
class MenuHelperTest extends HelperTestCase
{
    /**
     * Internal method to write auth data on session
     * @param array $data Data you want to write
     * @return void
     */
    protected function writeAuthOnSession(array $data = [])
    {
        $this->Helper->getView()->getRequest()->getSession()->write('Auth.User', $data);
        $this->Helper->Auth->initialize([]);
    }

    /**
     * Internal method to build links
     * @param array $links Links
     * @return array
     */
    protected function buildLinks(array $links): array
    {
        return array_map(function (array $link) {
            return call_user_func_array([$this->getMockForHelper(HtmlHelper::class, null), 'link'], $link);
        }, $links);
    }

    /**
     * Tests for `posts()` method
     * @test
     */
    public function testPosts()
    {
        [$links, $title, $options] = $this->Helper->posts();
        $this->assertEquals([
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ], $this->buildLinks($links));
        $this->assertEquals(I18N_POSTS, $title);
        $this->assertEquals(['icon' => 'far file-alt'], $options);

        $expected = [
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/posts-categories/add" title="Add category">Add category</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ];
        foreach (['manager', 'admin'] as $name) {
            $this->writeAuthOnSession(['group' => compact('name')]);
            $this->assertEquals($expected, $this->buildLinks($this->Helper->posts()[0]));
        }
    }

    /**
     * Tests for `pages()` method
     * @test
     */
    public function testPages()
    {
        [$links, $title, $options] = $this->Helper->pages();
        $this->assertEquals([
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
        ], $this->buildLinks($links));
        $this->assertEquals(I18N_PAGES, $title);
        $this->assertEquals(['icon' => 'far copy'], $options);

        $expected = [
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/add" title="Add page">Add page</a>',
            '<a href="/me-cms/admin/pages-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/pages-categories/add" title="Add category">Add category</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
        ];
        foreach (['manager', 'admin'] as $name) {
            $this->writeAuthOnSession(['group' => compact('name')]);
            $this->assertEquals($expected, $this->buildLinks($this->Helper->pages()[0]));
        }
    }

    /**
     * Tests for `photos()` method
     * @test
     */
    public function testPhotos()
    {
        [$links, $title, $options] = $this->Helper->photos();

        $expected = [
            '<a href="/me-cms/admin/photos" title="List photos">List photos</a>',
            '<a href="/me-cms/admin/photos/upload" title="Upload photos">Upload photos</a>',
            '<a href="/me-cms/admin/photos-albums" title="List albums">List albums</a>',
            '<a href="/me-cms/admin/photos-albums/add" title="Add album">Add album</a>',
        ];
        $this->assertEquals($expected, $this->buildLinks($links));
        $this->assertEquals(I18N_PHOTOS, $title);
        $this->assertEquals(['icon' => 'camera-retro'], $options);

        foreach (['manager', 'admin'] as $name) {
            $this->writeAuthOnSession(['group' => compact('name')]);
            $this->assertEquals($expected, $this->buildLinks($this->Helper->photos()[0]));
        }
    }

    /**
     * Tests for `banners()` method
     * @test
     */
    public function testBanners()
    {
        $this->assertEmpty($this->Helper->banners());

        $expected = [
            '<a href="/me-cms/admin/banners" title="List banners">List banners</a>',
            '<a href="/me-cms/admin/banners/upload" title="Upload banners">Upload banners</a>',
        ];

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links, $title, $options] = $this->Helper->banners();
        $this->assertEquals($expected, $this->buildLinks($links));
        $this->assertEquals('Banners', $title);
        $this->assertEquals(['icon' => 'shopping-cart'], $options);

        $expected[] = '<a href="/me-cms/admin/banners-positions" title="List positions">List positions</a>';
        $expected[] = '<a href="/me-cms/admin/banners-positions/add" title="Add position">Add position</a>';
        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->banners();
        $this->assertEquals($expected, $this->buildLinks($links));
    }

    /**
     * Tests for `users()` method
     * @test
     */
    public function testUsers()
    {
        $this->assertEmpty($this->Helper->users());

        $expected = [
            '<a href="/me-cms/admin/users" title="List users">List users</a>',
            '<a href="/me-cms/admin/users/add" title="Add user">Add user</a>',
        ];

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links, $title, $options] = $this->Helper->users();
        $this->assertEquals($expected, $this->buildLinks($links));
        $this->assertEquals('Users', $title);
        $this->assertEquals(['icon' => 'users'], $options);

        $expected[] = '<a href="/me-cms/admin/users-groups" title="List groups">List groups</a>';
        $expected[] = '<a href="/me-cms/admin/users-groups/add" title="Add group">Add group</a>';
        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->users();
        $this->assertEquals($expected, $this->buildLinks($links));
    }

    /**
     * Tests for `backups()` method
     * @test
     */
    public function testBackups()
    {
        $this->assertEmpty($this->Helper->backups());

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        $this->assertEmpty($this->Helper->backups());

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links, $title, $options] = $this->Helper->backups();
        $this->assertEquals([
            '<a href="/me-cms/admin/backups" title="List backups">List backups</a>',
            '<a href="/me-cms/admin/backups/add" title="Add backup">Add backup</a>',
        ], $this->buildLinks($links));
        $this->assertEquals('Backups', $title);
        $this->assertEquals(['icon' => 'database'], $options);
    }

    /**
     * Tests for `systems()` method
     * @test
     */
    public function testSystems()
    {
        $this->assertEmpty($this->Helper->systems());

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links, $title, $options] = $this->Helper->systems();
        $this->assertEquals([
            '<a href="/me-cms/admin/systems/tmp-viewer" title="Temporary files">Temporary files</a>',
            '<a href="/me-cms/admin/systems/checkup" title="System checkup">System checkup</a>',
            '<a href="/me-cms/admin/systems/browser" title="Media browser">Media browser</a>',
            '<a href="/me-cms/admin/systems/changelogs" title="Changelogs">Changelogs</a>',
        ], $this->buildLinks($links));
        $this->assertEquals('System', $title);
        $this->assertEquals(['icon' => 'wrench'], $options);

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        $this->assertEquals([
            '<a href="/me-cms/admin/systems/tmp-viewer" title="Temporary files">Temporary files</a>',
            '<a href="/me-cms/admin/systems/checkup" title="System checkup">System checkup</a>',
            '<a href="/me-cms/admin/systems/browser" title="Media browser">Media browser</a>',
            '<a href="/me-cms/admin/systems/changelogs" title="Changelogs">Changelogs</a>',
            '<a href="/me-cms/admin/logs" title="Log management">Log management</a>',
        ], $this->buildLinks($this->Helper->systems()[0]));
    }
}
