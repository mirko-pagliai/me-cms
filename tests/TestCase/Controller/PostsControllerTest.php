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
use MeCms\Model\Entity\Post;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PostsControllerTest class
 */
class PostsControllerTest extends ControllerTestCase
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
        $this->assertTemplate('Posts/index.ctp');
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('posts'));

        $cache = sprintf('index_limit_%s_page_%s', getConfigOrFail('default.records'), 1);
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
        $this->assertTemplate('Posts/index_by_date.ctp');
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('posts'));
        $this->assertEquals($date, $this->viewVariable('date'));

        $startFromView = $this->viewVariable('start');
        $this->assertInstanceof(Time::class, $startFromView);
        $this->assertEquals('2016-12-29 00:00:00', $startFromView->i18nFormat('yyyy-MM-dd HH:mm:ss'));

        $cache = sprintf(
            'index_date_%s_limit_%s_page_%s',
            md5(serialize([$startFromView, Time::parse($startFromView)->addDay(1)])),
            getConfigOrFail('default.records'),
            1
        );
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
            $this->assertTemplate('Posts/index_by_date.ctp');
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
        $expected = '/^\<item\>\<description\>Text of the seventh post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/seventh\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/seventh\-post\<\/link\>\<pubDate\>Thu, 29 Dec 2016 18\:59\:19 \+0000\<\/pubDate\>\<title\>Seventh post\<\/title\>\<\/item\>\<item\>\<description\>Text of the fifth post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/fifth\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/fifth\-post\<\/link\>\<pubDate\>Wed, 28 Dec 2016 18\:59\:19 \+0000\<\/pubDate\>\<title\>Fifth post\<\/title\>\<\/item\>\<item\>\<description\>Text of the fourth post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/fourth\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/fourth\-post\<\/link\>\<pubDate\>Wed, 28 Dec 2016 18\:58\:19 \+0000\<\/pubDate\>\<title\>Fourth post\<\/title\>\<\/item\>\<item\>\<description\>Text of the third post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/third\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/third\-post\<\/link\>\<pubDate\>Wed, 28 Dec 2016 18\:57\:19 \+0000\<\/pubDate\>\<title\>Third post\<\/title\>\<\/item\>\<item\>\<description\>&lt;img src\=&quot;http\:\/\/localhost\/thumb\/[\d\w]+&quot; alt\=&quot;[\d\w]+&quot; class\=&quot;img\-fluid&quot;\/&gt;&lt;br \/&gt;Text of the second post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/second\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/second\-post\<\/link\>\<pubDate\>Wed, 28 Dec 2016 18\:56\:19 \+0000\<\/pubDate\>\<title\>Second post\<\/title\>\<\/item\>\<item\>\<description\>Text of the first post\<\/description\>\<guid isPermaLink\="true"\>http\:\/\/localhost\/post\/first\-post\<\/guid\>\<link\>http\:\/\/localhost\/post\/first\-post\<\/link\>\<pubDate\>Mon, 28 Nov 2016 18\:55\:19 \+0000\<\/pubDate\>\<title\>First post\<\/title\>\<\/item\>$/';

        $this->get('/posts/rss');
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseRegExp($expected);
        $this->assertTemplate('Posts/rss/rss.ctp');
        $this->assertHeaderContains('Content-Type', 'application/rss+xml');
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('posts'));
    }

    /**
     * Tests for `rss()` method, using an invalid extension
     * @expectedException \Cake\Http\Exception\ForbiddenException
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
        $this->assertTemplate('Posts/search.ctp');
        $this->assertEmpty($this->viewVariable('posts'));
        $this->assertEmpty($this->viewVariable('pattern'));

        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('<span class="highlight">' . $pattern . '</span>');
        $this->assertEquals($this->viewVariable('pattern'), $pattern);
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('posts'));
        $this->assertContains($pattern, $this->viewVariable('posts')->first()->text);

        $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), getConfigOrFail('default.records_for_searches'), 1);
        list($postsFromCache, $pagingFromCache) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('posts')->toArray(), $postsFromCache->toArray());
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
        $slug = $this->Table->find('active')->where(['preview IS' => null])->extract('slug')->first();

        $this->get(['_name' => 'post', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts/view.ctp');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('related'));

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('post'), $cache->first());
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->setUserGroup('user');
        $slug = $this->Table->find('pending')->where(['preview IS' => null])->extract('slug')->first();

        $this->get(['_name' => 'postsPreview', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts/view.ctp');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('related'));
    }
}
