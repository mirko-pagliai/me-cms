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
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\Time;
use MeCms\Model\Entity\Post;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PostsControllerTest class
 * @property \MeCms\Controller\PostsController&\PHPUnit\Framework\MockObject\MockObject $Controller
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
     * @param \Cake\Event\EventInterface $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
        parent::controllerSpy($event, $controller);

        if ($this->getName() === 'testRss') {
            $this->_controller->viewBuilder()->setLayout(null);
        }
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex(): void
    {
        $url = ['_name' => 'posts'];
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));

        $cache = sprintf('index_limit_%s_page_%s', getConfigOrFail('default.records'), 1);
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
    }

    /**
     * Tests for `indexByDate()` method
     * @test
     */
    public function testIndexByDate(): void
    {
        $date = '2016/12/29';
        $url = ['_name' => 'postsByDate', $date];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts' . DS . 'index_by_date.php');
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));
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
        [$postsFromCache, $pagingFromCache] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('posts')->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
//        dd($this->_controller->getRequest());
        $this->assertNotEmpty($this->_controller->getPaging()['Posts']);

        //Tries with various possible dates
        foreach (['today', 'yesterday', '2016', '2016/12', '2016/12/29'] as $date) {
            $this->get(['_name' => 'postsByDate', $date]);
            $this->assertResponseOkAndNotEmpty();
            $this->assertTemplate('Posts' . DS . 'index_by_date.php');
        }

        //GET request with query string
        $this->get($url + ['?' => ['q' => $date]]);
        $this->assertRedirect($url);
    }

    /**
     * Tests for `rss()` method
     * @test
     */
    public function testRss(): void
    {
        $this->get('/posts/rss');
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseRegExp('/^\<\?xml version\="1\.0" encoding\="UTF\-8"\?\>\n\<rss xmlns\:content\="http\:\/\/purl\.org\/rss\/1\.0\/modules\/content\/" version\="2\.0"\>\n\s*\<channel\>/');
        $this->assertHeaderContains('Content-Type', 'application/rss+xml');
        $data = $this->viewVariable('data');
        $this->assertArrayKeysEqual(['channel', 'items'], $data);
        $this->assertNotEmpty($data['items'][0]);

        //With an invalid extension
        $this->expectException(ForbiddenException::class);
        $this->Controller->setRequest($this->Controller->getRequest()->withParam('_ext', 'html'))->rss();
    }

    /**
     * Tests for `search()` method
     * @test
     */
    public function testSearch(): void
    {
        $pattern = 'Text of the seventh';
        $url = ['_name' => 'postsSearch'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts' . DS . 'search.php');
        $this->assertEmpty($this->viewVariable('posts'));
        $this->assertEmpty($this->viewVariable('pattern'));

        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('<span class="highlight">' . $pattern . '</span>');
        $this->assertEquals($this->viewVariable('pattern'), $pattern);
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));
        $this->assertStringContainsString($pattern, $this->viewVariable('posts')->first()->text);

        $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), getConfigOrFail('default.records_for_searches'), 1);
        [$postsFromCache, $pagingFromCache] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Table->getCacheName()
        ));
        $this->assertEquals($this->viewVariable('posts')->toArray(), $postsFromCache->toArray());
        $this->assertNotEmpty($pagingFromCache['Posts']);

        //GET request again. Now the data is in cache
        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('<span class="highlight">' . $pattern . '</span>');
        $this->assertNotEmpty($this->_controller->getPaging()['Posts']);

        $this->get($url + ['?' => ['p' => 'a']]);
        $this->assertRedirect($url);
        $this->assertFlashMessage('You have to search at least a word of 4 characters');

        $this->session(['last_search' => ['id' => md5((string)time()), 'time' => time()]]);
        $this->get($url + ['?' => ['p' => $pattern]]);
        $this->assertRedirect($url);
        $this->assertFlashMessage('You have to wait 10 seconds to perform a new search');
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView(): void
    {
        $this->get(['_name' => 'post', 'first-post']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts' . DS . 'view.php');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('related'));
        $cache = Cache::read('view_' . md5('first-post'), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('post'), $cache->first());
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview(): void
    {
        $this->setUserGroup('user');
        $this->get(['_name' => 'postsPreview', 'inactive-post']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Posts' . DS . 'view.php');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('related'));
    }
}
