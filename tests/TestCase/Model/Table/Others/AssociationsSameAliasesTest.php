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
namespace MeCms\Test\TestCase\Model\Table\Others;

use MeCms\Model\Table\PagesTable;
use MeCms\Model\Table\PostsTable;
use MeCms\TestSuite\TableTestCase;

/**
 * AssociationsSameAliasesTest class
 */
class AssociationsSameAliasesTest extends TableTestCase
{
    /**
     * If `true`, a mock instance of the table will be created
     * @var bool
     */
    protected $autoInitializeClass = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
    ];

    /**
     * Test for associations with the same alias
     * @test
     */
    public function testAssociationsSameAliases()
    {
        $tables[] = $this->getMockForTable(PagesTable::class, null);
        $tables[] = $this->getMockForTable(PostsTable::class, null);

        foreach ($tables as $table) {
            $categories = $table->Categories;

            $this->assertBelongsTo($categories);
            $this->assertEquals('Categories', $categories->getName());
            $this->assertEquals(sprintf('%s.%sCategories', ME_CMS, $table->getAlias()), $categories->className());

            $this->assertInstanceof(sprintf('%s\Model\Entity\%sCategory', ME_CMS, $table->getAlias()), $categories->find()->first());
        }
    }
}
