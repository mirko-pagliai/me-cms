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
    public function testIndex(): void
    {
        $url = ['_name' => 'postsTags'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PostsTags' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(Tag::class, $this->viewVariable('tags'));

        $cache = sprintf('tags_limit_%s_page_%s', getConfigOrFail('default.records') * 4, 1);
        [$tagsFromCache, $pagingFromCache] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('tags')->toArray(), $tagsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Tags']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->getPaging()['Tags']);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView(): void
    {
        $url = ['_name' => 'postsTag', 'cat'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('PostsTags' . DS . 'view.php');
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));
        $this->assertInstanceof(Tag::class, $this->viewVariable('tag'));

        $tagFromCache = Cache::read('tag_' . md5('cat'), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('tag'), $tagFromCache->first());

        $cache = sprintf('tag_%s_limit_%s_page_%s', md5('cat'), getConfigOrFail('default.records'), 1);
        [$postsFromCache, $pagingFromCache] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('posts')->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertNotEmpty($this->_controller->getPaging()['Posts']);

        //GET request with query string
        $this->get($url + ['?' => ['q' => 'cat']]);
        $this->assertRedirect($url);

        //GET request with a no existing tag
        $this->get(['_name' => 'postsTag', 'no-existing']);
        $this->assertResponseError();
    }
}
