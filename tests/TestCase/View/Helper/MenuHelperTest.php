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
     * @return string
     */
    protected function buildLinks(array $links): string
    {
        return implode(PHP_EOL, array_map(function (array $link) {
            return call_user_func_array([$this->getMockForHelper(HtmlHelper::class, null), 'link'], $link);
        }, $links));
    }

    /**
     * Tests for `posts()` method
     * @test
     */
    public function testPosts()
    {
        [$links,,, $handledControllers] = $this->Helper->posts();
        $links = $this->buildLinks($links);
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List categories', $links);
        $this->assertTextNotContains('Add category', $links);
        $this->assertEquals(['Posts', 'PostsCategories', 'PostsTags'], $handledControllers);

        foreach (['manager', 'admin'] as $name) {
            $this->writeAuthOnSession(['group' => compact('name')]);
            [$links] = $this->Helper->posts();
            $links = $this->buildLinks($links);
            $this->assertTextContains('List categories', $links);
            $this->assertTextContains('Add category', $links);
        }
    }

    /**
     * Tests for `pages()` method
     * @test
     */
    public function testPages()
    {
        [$links,,, $handledControllers] = $this->Helper->pages();
        $links = $this->buildLinks($links);
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List categories', $links);
        $this->assertTextNotContains('Add category', $links);
        $this->assertEquals(['Pages', 'PagesCategories'], $handledControllers);

        foreach (['manager', 'admin'] as $name) {
            $this->writeAuthOnSession(['group' => compact('name')]);
            [$links] = $this->Helper->pages();
            $links = $this->buildLinks($links);
            $this->assertTextContains('List categories', $links);
            $this->assertTextContains('Add category', $links);
        }
    }

    /**
     * Tests for `photos()` method
     * @test
     */
    public function testPhotos()
    {
        [$links,,, $handledControllers] = $this->Helper->photos();
        $this->assertNotEmpty($this->buildLinks($links));
        $this->assertEquals(['Photos', 'PhotosAlbums'], $handledControllers);
    }

    /**
     * Tests for `banners()` method
     * @test
     */
    public function testBanners()
    {
        $this->assertEmpty($this->Helper->banners());

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links,,, $handledControllers] = $this->Helper->banners();
        $links = $this->buildLinks($links);
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List positions', $links);
        $this->assertTextNotContains('Add position', $links);
        $this->assertEquals(['Banners', 'BannersPositions'], $handledControllers);

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->banners();
        $links = $this->buildLinks($links);
        $this->assertTextContains('List positions', $links);
        $this->assertTextContains('Add position', $links);
    }

    /**
     * Tests for `users()` method
     * @test
     */
    public function testUsers()
    {
        $this->assertEmpty($this->Helper->users());

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links,,, $handledControllers] = $this->Helper->users();
        $links = $this->buildLinks($links);
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List groups', $links);
        $this->assertTextNotContains('Add group', $links);
        $this->assertEquals(['Users', 'UsersGroups'], $handledControllers);

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->users();
        $links = $this->buildLinks($links);
        $this->assertTextContains('List groups', $links);
        $this->assertTextContains('Add group', $links);
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
        [$links,,, $handledControllers] = $this->Helper->backups();
        $this->assertNotEmpty($this->buildLinks($links));
        $this->assertEquals(['Backups'], $handledControllers);
    }

    /**
     * Tests for `systems()` method
     * @test
     */
    public function testSystems()
    {
        $this->assertEmpty($this->Helper->systems());

        $this->writeAuthOnSession(['group' => ['name' => 'manager']]);
        [$links,,, $handledControllers] = $this->Helper->systems();
        $links = $this->buildLinks($links);
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('Log management', $links);
        $this->assertEquals(['Logs', 'Systems'], $handledControllers);

        $this->writeAuthOnSession(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->systems();
        $this->assertTextContains('Log management', $this->buildLinks($links));
    }
}
