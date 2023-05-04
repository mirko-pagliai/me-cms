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

namespace MeCms\Test\TestCase\View\Helper\MenuHelper;

use MeCms\TestSuite\MenuHelperTestCase;

/**
 * PostsMenuHelperTest class
 */
class PostsMenuHelperTest extends MenuHelperTestCase
{
    /**
     * @test
     * @uses \MeCms\View\Helper\MenuHelper\PostsMenuHelper::getLinks()
     */
    public function testGetLinks(): void
    {
        $expected = [
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ];
        $this->assertSame($expected, $this->getLinksAsHtml());

        $expected = [
            '<a href="/admin" title="List posts">List posts</a>',
            '<a href="/me-cms/admin/posts/add" title="Add post">Add post</a>',
            '<a href="/me-cms/admin/posts-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/posts-categories/add" title="Add category">Add category</a>',
            '<a href="/me-cms/admin/posts-tags" title="List tags">List tags</a>',
        ];
        $this->setIdentity(['group' => ['name' => 'admin']]);
        $this->assertSame($expected, $this->getLinksAsHtml());

        $this->setIdentity(['group' => ['name' => 'manager']]);
        $this->assertSame($expected, $this->getLinksAsHtml());
    }
}
