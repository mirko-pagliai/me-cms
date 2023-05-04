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
 * PagesMenuHelperTest class
 */
class PagesMenuHelperTest extends MenuHelperTestCase
{
    /**
     * @test
     * @uses \MeCms\View\Helper\MenuHelper\PagesMenuHelper::getLinks()
     */
    public function testGetLinks(): void
    {
        $expected = [
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
        ];
        $this->assertSame($expected, $this->getLinksAsHtml());

        $expected = [
            '<a href="/me-cms/admin/pages" title="List pages">List pages</a>',
            '<a href="/me-cms/admin/pages/add" title="Add page">Add page</a>',
            '<a href="/me-cms/admin/pages/index-statics" title="List static pages">List static pages</a>',
            '<a href="/me-cms/admin/pages-categories" title="List categories">List categories</a>',
            '<a href="/me-cms/admin/pages-categories/add" title="Add category">Add category</a>',
        ];
        $this->setIdentity(['group' => ['name' => 'admin']]);
        $this->assertSame($expected, $this->getLinksAsHtml());

        $this->setIdentity(['group' => ['name' => 'manager']]);
        $this->assertSame($expected, $this->getLinksAsHtml());
    }
}
