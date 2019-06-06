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

use MeCms\TestSuite\TableTestCase;

/**
 * AssociationsSameAliasesTest class
 */
class AssociationsSameAliasesTest extends TableTestCase
{
    /**
     * @var bool
     */
    protected $autoInitializeClass = false;

    /**
     * Fixtures
     * @var array
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
    public function testAssociationsSameAliases()
    {
        $tables['Pages'] = $this->getMockForModel('MeCms.Pages', null);
        $tables['Posts'] = $this->getMockForModel('MeCms.Posts', null);

        foreach ($tables as $table) {
            $categories = $table->Categories;

            $this->assertBelongsTo($categories);
            $this->assertEquals('Categories', $categories->getName());
            $this->assertEquals(sprintf('%s.%sCategories', 'MeCms', $table->getAlias()), $categories->getClassName());

            $this->assertInstanceof(sprintf('%s\Model\Entity\%sCategory', 'MeCms', $table->getAlias()), $categories->find()->first());
        }
    }
}
