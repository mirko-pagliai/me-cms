<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\PostsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;
use Reflection\ReflectionTrait;

/**
 * PostsControllerTest class
 */
class PostsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;
    use ReflectionTrait;

    /**
     * @var \MeCms\Controller\PostsController
     */
    protected $Controller;

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

        $this->Controller = new PostsController;
        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        Cache::clear(false, $this->Posts->cache);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Posts);
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $url = ['_name' => 'posts'];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/index.ctp');

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\ResultSet', $postsFromView);
        $this->assertNotEmpty($postsFromView);

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }

        //Sets the cache name
        $cache = sprintf('index_limit_%s_page_%s', config('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOk();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);
    }

    /**
     * Tests for `indexByDate()` method
     * @test
     */
    public function testIndexByDate()
    {
        $date = '2016/12/29';
        $url = ['_name' => 'postsByDate', $date];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/index_by_date.ctp');

        $dateFromView = $this->viewVariable('date');
        $this->assertEquals($date, $dateFromView);

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\ResultSet', $postsFromView);
        $this->assertNotEmpty($postsFromView->toArray());

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }

        $startFromView = $this->viewVariable('start');
        $this->assertInstanceof('Cake\I18n\Time', $startFromView);
        $this->assertEquals('2016-12-29 00:00:00', $startFromView->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Sets the cache name
        $end = Time::parse($startFromView)->addDay(1);
        $cache = sprintf('index_date_%s_limit_%s_page_%s', md5(serialize([$startFromView, $end])), config('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOk();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        //Tries with various possible dates
        foreach ([
            'today',
            'yesterday',
            '2016',
            '2016/12',
            '2016/12/29',
        ] as $date) {
            $this->get(['_name' => 'postsByDate', $date]);
            $this->assertResponseOk();
            $this->assertResponseNotEmpty();
            $this->assertTemplate(ROOT . 'src/Template/Posts/index_by_date.ctp');
        }

        //GET request with query string
        $this->get(array_merge($url, ['?' => ['q' => $date]]));
        $this->assertRedirect($url);
    }

    /**
     * Tests for `rss()` method
     * @test
     */
    public function testRss()
    {
        $this->get('/posts/rss');
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/rss/rss.ctp');

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\Query', $postsFromView);
        $this->assertNotEmpty($postsFromView->toArray());

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }

        $this->assertHeaderContains('Content-Type', 'application/rss+xml');
    }

    /**
     * Tests for `rss()` method, using an invalid extension
     * @expectedException \Cake\Network\Exception\ForbiddenException
     * @test
     */
    public function testRssInvalidExtension()
    {
        $this->Controller->request = $this->Controller->request->withParam('_ext', 'html');
        $this->Controller->rss();
    }

    /**
     * Tests for `search()` method
     * @test
     */
    public function testSearch()
    {
        $pattern = 'Text of the seventh';
        $url = ['_name' => 'postsSearch'];

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/search.ctp');

        $this->assertEmpty($this->viewVariable('posts'));
        $this->assertEmpty($this->viewVariable('pattern'));

        $this->get(array_merge($url, ['?' => ['p' => $pattern]]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();

        $postsFromView = $this->viewVariable('posts');
        $this->assertInstanceof('Cake\ORM\ResultSet', $postsFromView);
        $this->assertNotEmpty($postsFromView->toArray());

        foreach ($postsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
            $this->assertContains($pattern, $post->text);
        }

        $this->assertEquals($this->viewVariable('pattern'), $pattern);

        //Sets the cache name
        $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), config('default.records_for_searches'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get(array_merge($url, ['?' => ['p' => $pattern]]));
        $this->assertResponseOk();
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        $this->get(array_merge($url, ['?' => ['p' => 'a']]));
        $this->assertRedirect($url);
        $this->assertSession('You have to search at least a word of 4 characters', 'Flash.flash.0.message');

        $this->session(['last_search' => [
            'id' => md5(time()),
            'time' => time(),
        ]]);

        $this->get(array_merge($url, ['?' => ['p' => $pattern]]));
        $this->assertRedirect($url);
        $this->assertSession('You have to wait 10 seconds to perform a new search', 'Flash.flash.0.message');
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Posts->find('active')
            ->where(['preview IS' => null])
            ->extract('slug')
            ->first();

        $this->get(['_name' => 'post', $slug]);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/view.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Posts->cache);
        $this->assertEquals($postFromView, $cache->first());

        $relatedPostsFromView = $this->viewVariable('related');
        $this->assertNotEmpty($relatedPostsFromView);

        foreach ($relatedPostsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->setUserGroup('user');

        $slug = $this->Posts->find('pending')
            ->where(['preview IS' => null])
            ->extract('slug')
            ->first();

        $this->get(['_name' => 'postsPreview', $slug]);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/view.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        $relatedPostsFromView = $this->viewVariable('related');
        $this->assertNotEmpty($relatedPostsFromView);

        foreach ($relatedPostsFromView as $post) {
            $this->assertInstanceof('MeCms\Model\Entity\Post', $post);
        }
    }
}
