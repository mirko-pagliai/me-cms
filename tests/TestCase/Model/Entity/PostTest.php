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
namespace MeCms\Test\TestCase\Model\Entity;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Model\Entity\Post;
use MeTools\TestSuite\TestCase;

/**
 * PostTest class
 */
class PostTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\Post
     */
    protected $Post;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

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

        $this->Post = new Post;
        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->Post->isAccessible('id'));
        $this->assertFalse($this->Post->isAccessible('preview'));
        $this->assertFalse($this->Post->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['tags_as_string'], $this->Post->getVirtual());
    }

    /**
     * Test for `_getTagsAsString()` method
     * @test
     */
    public function testTagsAsStringGetMutator()
    {
        $post = $this->Posts->findById(1)->contain(['Tags'])->first();
        $this->assertEquals('cat, dog, bird', $post->tags_as_string);

        $post = $this->Posts->findById(3)->contain(['Tags'])->first();
        $this->assertEquals('cat', $post->tags_as_string);

        $post = $this->Posts->findById(4)->contain(['Tags'])->first();
        $this->assertNull($post->tags_as_string);
    }
}
