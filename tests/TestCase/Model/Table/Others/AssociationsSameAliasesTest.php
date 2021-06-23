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
    public function testAssociationsSameAliases(): void
    {
        foreach (['Pages', 'Posts'] as $name) {
            /** @var \MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable $Table */
            $Table = $this->getTable('MeCms.' . $name);
            $this->assertBelongsTo($Table->Categories);
            $this->assertEquals('Categories', $Table->Categories->getName());
            $this->assertEquals('MeCms\\Model\\Table\\' . $name . 'CategoriesTable', $Table->Categories->getClassName());
            /** @var class-string<\MeCms\Model\Table\PagesTable|\MeCms\Model\Table\PostsTable> $className */
            $className = '\\MeCms\\Model\\Entity\\' . $name . 'Category';
            $this->assertInstanceof($className, $Table->Categories->find()->first());
        }
    }
}
