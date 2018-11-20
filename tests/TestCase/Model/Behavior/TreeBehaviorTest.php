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
namespace MeCms\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use MeCms\TestSuite\TestCase;

/**
 * TreeBehaviorTest class
 */
class TreeBehaviorTest extends TestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.PostsCategories',
    ];

    /**
     * Test for `findTreeList()` method
     * @test
     */
    public function testFindTreeList()
    {
        $table = TableRegistry::get(ME_CMS . '.PostsCategories');

        $expected = [
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ];
        $this->assertEquals($expected, $table->find('treeList')->toArray());

        $expected = [
            1 => 'First post category',
            3 => '_Sub post category',
            4 => '__Sub sub post category',
            2 => 'Another post category',
        ];
        $this->assertEquals($expected, $table->find('treeList', ['spacer' => '_'])->toArray());
    }
}
