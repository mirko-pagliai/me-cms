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
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\Tag;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PostsTagsControllerTest class
 */
class PostsTagsControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
        'plugin.MeCms.Users',
    ];

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $url = ['_name' => 'postsTags'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PostsTags' . DS . 'index.ctp');
        $this->assertContainsOnlyInstancesOf(Tag::class, $this->viewVariable('tags'));

        $cache = sprintf('tags_limit_%s_page_%s', getConfigOrFail('default.records') * 4, 1);
        list($tagsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('tags')->toArray(), $tagsFromCache->toArray());
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
        $slug = $this->Table->Tags->find('active')->extract('slug')->first();
        $url = ['_name' => 'postsTag', $slug];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PostsTags' . DS . 'view.ctp');
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));
        $this->assertInstanceof(Tag::class, $this->viewVariable('tag'));

        $tagFromCache = Cache::read(sprintf('tag_%s', md5($slug)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('tag'), $tagFromCache->first());

        $cache = sprintf('tag_%s_limit_%s_page_%s', md5($slug), getConfigOrFail('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('posts')->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        //GET request with query string
        $this->get($url + ['?' => ['q' => $slug]]);
        $this->assertRedirect($url);

        //GET request with a no existing tag
        $this->get(['_name' => 'postsTag', 'no-existing']);
        $this->assertResponseError();
    }
}
