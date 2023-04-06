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

namespace MeCms\Test\TestCase\Model\Table\Others;

use MeCms\TestSuite\TableTestCase;

/**
 * AssociationsSameAliasesTest class
 */
class AssociationsSameAliasesTest extends TableTestCase
{
    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * Test for associations with the same alias
     * @test
     */
    public function testAssociationsSameAliases(): void
    {
        foreach (['Pages', 'Posts'] as $name) {
            /** @var \MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable $Table */
            $Table = $this->getTable('MeCms.' . $name);
            $this->assertBelongsTo($Table->Categories);
            $this->assertSame('Categories', $Table->Categories->getName());
            $this->assertSame('MeCms\\Model\\Table\\' . $name . 'CategoriesTable', $Table->Categories->getClassName());
            $this->assertInstanceOf('\\MeCms\\Model\\Entity\\' . $name . 'Category', $Table->Categories->find()->first());
        }
    }
}
