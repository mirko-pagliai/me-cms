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
namespace MeCms\Test\TestCase\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeTools\TestSuite\TestCase;

/**
 * PostsTableTest class
 */
class PostsTagsTableTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTagsTable
     */
    protected $PostsTags;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->PostsTags = TableRegistry::get(ME_CMS . '.PostsTags');

        Cache::clear(false, $this->PostsTags->cache);
    }

    /**
     * Test for `cache` property
     * @test
     */
    public function testCacheProperty()
    {
        $this->assertEquals('posts', $this->PostsTags->cache);
    }

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules()
    {
        $entity = $this->PostsTags->newEntity(['tag_id' => 999, 'post_id' => 999]);
        $this->assertFalse($this->PostsTags->save($entity));

        $this->assertEquals([
            'tag_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
            'post_id' => ['_existsIn' => I18N_SELECT_VALID_OPTION],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertEquals('posts_tags', $this->PostsTags->getTable());
        $this->assertEquals('id', $this->PostsTags->getDisplayField());
        $this->assertEquals('id', $this->PostsTags->getPrimaryKey());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->PostsTags->Posts);
        $this->assertEquals('post_id', $this->PostsTags->Posts->getForeignKey());
        $this->assertEquals('INNER', $this->PostsTags->Posts->getJoinType());
        $this->assertEquals(ME_CMS . '.Posts', $this->PostsTags->Posts->className());

        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->PostsTags->Tags);
        $this->assertEquals('tag_id', $this->PostsTags->Tags->getForeignKey());
        $this->assertEquals('INNER', $this->PostsTags->Tags->getJoinType());
        $this->assertEquals(ME_CMS . '.Tags', $this->PostsTags->Tags->className());

        $this->assertTrue($this->PostsTags->hasBehavior('CounterCache'));

        $this->assertInstanceOf('MeCms\Model\Validation\PostsTagValidator', $this->PostsTags->validator());
    }
}
