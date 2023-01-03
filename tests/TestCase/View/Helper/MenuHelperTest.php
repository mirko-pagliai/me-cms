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

use MeCms\TestSuite\MenuHelperTestCase;

/**
 * MenuHelperTest class
 * @property \MeCms\View\Helper\MenuHelper $Helper
 */
class MenuHelperTest extends MenuHelperTestCase
{
    /**
     * Tests for `posts()` method
     * @uses \MeCms\View\Helper\MenuHelper::posts()
     * @test
     */
    public function testPosts(): void
    {
        [$links,,, $handledControllers] = $this->Helper->posts();
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List categories', $links);
        $this->assertTextNotContains('Add category', $links);
        $this->assertEquals(['Posts', 'PostsCategories', 'PostsTags'], $handledControllers);

        foreach (['manager', 'admin'] as $name) {
            $this->setIdentity(['group' => compact('name')]);
            [$links] = $this->Helper->posts();
            $this->assertTextContains('List categories', $links);
            $this->assertTextContains('Add category', $links);
        }
    }

    /**
     * Tests for `pages()` method
     * @uses \MeCms\View\Helper\MenuHelper::pages()
     * @test
     */
    public function testPages(): void
    {
        [$links,,, $handledControllers] = $this->Helper->pages();
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List categories', $links);
        $this->assertTextNotContains('Add category', $links);
        $this->assertEquals(['Pages', 'PagesCategories'], $handledControllers);

        foreach (['manager', 'admin'] as $name) {
            $this->setIdentity(['group' => compact('name')]);
            [$links] = $this->Helper->pages();
            $this->assertTextContains('List categories', $links);
            $this->assertTextContains('Add category', $links);
        }
    }

    /**
     * Tests for `users()` method
     * @uses \MeCms\View\Helper\MenuHelper::users()
     * @test
     */
    public function testUsers(): void
    {
        $this->assertEmpty($this->Helper->users());

        $this->setIdentity(['group' => ['name' => 'manager']]);
        [$links,,, $handledControllers] = $this->Helper->users();
        $this->assertNotEmpty($links);
        $this->assertTextNotContains('List groups', $links);
        $this->assertTextNotContains('Add group', $links);
        $this->assertEquals(['Users', 'UsersGroups'], $handledControllers);

        $this->setIdentity(['group' => ['name' => 'admin']]);
        [$links] = $this->Helper->users();
        $this->assertTextContains('List groups', $links);
        $this->assertTextContains('Add group', $links);
    }

    /**
     * Tests for `systems()` method
     * @uses \MeCms\View\Helper\MenuHelper::systems()
     * @test
     */
    public function testSystems(): void
    {
        $this->assertEmpty($this->Helper->systems());

        foreach (['manager', 'admin'] as $name) {
            $this->setIdentity(['group' => compact('name')]);
            [$links,,, $handledControllers] = $this->Helper->systems();
            $this->assertNotEmpty($links);
            $this->assertEquals(['Systems'], $handledControllers);
        }
    }
}
