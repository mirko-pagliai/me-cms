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
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PostsTagsControllerTest class
 */
class PostsTagsControllerTest extends IntegrationTestCase
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
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
        'plugin.me_cms.users',
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
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $url = ['_name' => 'postsTags'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsTags/index.ctp');

        $tagsFromView = $this->viewVariable('tags');
        $this->assertNotEmpty($tagsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagsFromView);

        //Sets the cache name
        $cache = sprintf('tags_limit_%s_page_%s', getConfigOrFail('default.records') * 4, 1);
        list($tagsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->cache
        ));

        $this->assertEquals($tagsFromView->toArray(), $tagsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Tags']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Tags']);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->PostsTags->Tags->find('active')->extract('slug')->first();
        $url = ['_name' => 'postsTag', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/PostsTags/view.ctp');

        $tagFromView = $this->viewVariable('tag');
        $this->assertNotEmpty($tagFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $tagFromView);

        $tagFromCache = Cache::read((sprintf('tag_%s', md5($slug))), $this->PostsTags->cache);
        $this->assertEquals($tagFromView, $tagFromCache->first());

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);

        //Sets the cache name
        $cache = sprintf('tag_%s_limit_%s_page_%s', md5($slug), getConfigOrFail('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        //GET request with query string
        $this->get(array_merge($url, ['?' => ['q' => $slug]]));
        $this->assertRedirect($url);

        //GET request with a no existing tag
        $this->get(['_name' => 'postsTag', 'no-existing']);
        $this->assertResponseError();
    }
}
