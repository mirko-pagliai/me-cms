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
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsTags',
        'plugin.me_cms.Tags',
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
        $this->assertEquals(['plain_text', 'tags_as_string'], $this->Post->getVirtual());
    }

    /**
     * Test for `_getPlainText()` method
     * @test
     */
    public function testPlainTextGetMutator()
    {
        $this->assertEquals('Text of the first post', $this->Posts->find()->extract('plain_text')->first());
        $this->assertEmpty((new Post)->plain_text);
    }

    /**
     * Test for `_getTagsAsString()` method
     * @test
     */
    public function testTagsAsStringGetMutator()
    {
        foreach ([
            1 => 'cat, dog, bird',
            3 => 'cat',
            4 => null,
        ] as $postId => $expectedTags) {
            $result = $this->Posts->findById($postId)->contain('Tags')->extract('tags_as_string')->first();
            $this->assertEquals($expectedTags, $result);
        }
    }
}
