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
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use MeCms\Controller\PostsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PostsControllerTest class
 */
class PostsControllerTest extends IntegrationTestCase
{
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
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        if ($this->getName() === 'testRss') {
            $this->_controller->viewBuilder()->setLayout(false);
        }
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $url = ['_name' => 'posts'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/index.ctp');

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);

        //Sets the cache name
        $cache = sprintf('index_limit_%s_page_%s', getConfigOrFail('default.records'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/index_by_date.ctp');

        $dateFromView = $this->viewVariable('date');
        $this->assertEquals($date, $dateFromView);

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView->toArray());
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);

        $startFromView = $this->viewVariable('start');
        $this->assertInstanceof('Cake\I18n\Time', $startFromView);
        $this->assertEquals('2016-12-29 00:00:00', $startFromView->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        //Sets the cache name
        $end = Time::parse($startFromView)->addDay(1);
        $cache = sprintf(
            'index_date_%s_limit_%s_page_%s',
            md5(serialize([$startFromView, $end])),
            getConfigOrFail('default.records'),
            1
        );
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
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
            $this->assertResponseOkAndNotEmpty();
            $this->assertTemplate(ROOT . 'src/Template/Posts/index_by_date.ctp');
        }

        //GET request with query string
        $this->get($url + ['?' => ['q' => $date]]);
        $this->assertRedirect($url);
    }

    /**
     * Tests for `rss()` method
     * @test
     */
    public function testRss()
    {
        $this->get('/posts/rss');
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/rss/rss.ctp');
        $this->assertHeaderContains('Content-Type', 'application/rss+xml');

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView->toArray());
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);
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
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/search.ctp');

        $this->assertEmpty($this->viewVariable('posts'));
        $this->assertEmpty($this->viewVariable('pattern'));

        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('<span class="highlight">' . $pattern . '</span>');

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);
        $this->assertContains($pattern, $postsFromView->toArray()[0]->text);

        $this->assertEquals($this->viewVariable('pattern'), $pattern);

        //Sets the cache name
        $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), getConfigOrFail('default.records_for_searches'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        $this->assertEquals($postsFromView->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('<span class="highlight">' . $pattern . '</span>');
        $this->assertNotEmpty($this->_controller->request->getParam('paging')['Posts']);

        $this->get($url + ['?' => ['p' => 'a']]);
        $this->assertRedirect($url);
        $this->assertFlashMessage('You have to search at least a word of 4 characters');

        $this->session(['last_search' => ['id' => md5(time()), 'time' => time()]]);

        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertRedirect($url);
        $this->assertFlashMessage('You have to wait 10 seconds to perform a new search');
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Posts->find('active')->where(['preview IS' => null])->extract('slug')->first();

        $this->get(['_name' => 'post', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/view.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Posts->cache);
        $this->assertEquals($postFromView, $cache->first());

        $relatedPostsFromView = $this->viewVariable('related');
        $this->assertNotEmpty($relatedPostsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $relatedPostsFromView);
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->setUserGroup('user');

        $slug = $this->Posts->find('pending')->where(['preview IS' => null])->extract('slug')->first();

        $this->get(['_name' => 'postsPreview', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Posts/view.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        $relatedPostsFromView = $this->viewVariable('related');
        $this->assertNotEmpty($relatedPostsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $relatedPostsFromView);
    }
}
