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

namespace MeCms\Controller;

use Cake\Cache\Cache;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\I18n\I18n;
use Cake\ORM\ResultSet;
use Cake\Routing\Router;
use Cake\Utility\Text;
use MeCms\Controller\Traits\CheckLastSearchTrait;
use MeCms\Controller\Traits\GetStartAndEndDateTrait;
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\Tag;
use Tools\Exceptionist;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    use CheckLastSearchTrait;
    use GetStartAndEndDateTrait;

    /**
     * Initialization hook method
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        if ($this->getRequest()->is('action', 'preview')) {
            $this->Authentication->setConfig('requireIdentity', true);
        }
    }

    /**
     * Lists posts
     * @return void
     */
    public function index(): void
    {
        //Tries to get data from the cache
        $cache = sprintf('index_limit_%s_page_%s', $this->paginate['limit'], $this->getQueryPage());
        $posts = Cache::read($cache, $this->Posts->getCacheName());
        $paging = Cache::read($cache . '_paging', $this->Posts->getCacheName());

        //If the data are not available from the cache
        if (!$posts || !$paging) {
            $query = $this->Posts->find('active')->find('forIndex');

            [$posts, $paging] = [$this->paginate($query), $this->getPaging()];

            Cache::writeMany([$cache => $posts, $cache . '_paging' => $paging], $this->Posts->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setPaging($paging);
        }

        $this->set(compact('posts'));
    }

    /**
     * Lists posts for a specific date.
     *
     * Month and day are optional, and you can also use special keywords `today` or `yesterday`.
     *
     * Examples:
     * <pre>/posts/2016/06/11</pre>
     * <pre>/posts/2016/06</pre>
     * <pre>/posts/2016</pre>
     * <pre>/posts/today</pre>
     * <pre>/posts/yesterday</pre>
     * @param string $date Date as `today`, `yesterday`, `YYYY/MM/dd`, `YYYY/MM` or `YYYY`
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Controller\Traits\GetStartAndEndDateTrait::getStartAndEndDate()
     */
    public function indexByDate(string $date)
    {
        //Data can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        [$start, $end] = $this->getStartAndEndDate($date);

        //Tries to get data from the cache
        $cache = sprintf('index_date_%s_limit_%s_page_%s', md5(serialize([$start, $end])), $this->paginate['limit'], $this->getQueryPage());
        $posts = Cache::read($cache, $this->Posts->getCacheName());
        $paging = Cache::read($cache . '_paging', $this->Posts->getCacheName());

        //If the data are not available from the cache
        if (!$posts || !$paging) {
            $query = $this->Posts->find('active')
                ->find('forIndex')
                ->where([$this->Posts->getAlias() . '.created >=' => $start])
                ->andWhere([$this->Posts->getAlias() . '.created <' => $end]);

            [$posts, $paging] = [$this->paginate($query), $this->getPaging()];

            Cache::writeMany([$cache => $posts, $cache . '_paging' => $paging], $this->Posts->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setPaging($paging);
        }

        $this->set(compact('date', 'posts', 'start'));
    }

    /**
     * Lists posts as RSS
     * @return void
     * @throws \Cake\Http\Exception\ForbiddenException
     */
    public function rss(): void
    {
        //This method works only for RSS
        if (!$this->RequestHandler->prefers('rss')) {
            throw new ForbiddenException();
        }
        $this->viewBuilder()->setClassName('Feed.Rss');

        $posts = $this->Posts->find('active')
            ->contain(['Categories' => ['fields' => ['title']]])
            ->limit(getConfigOrFail('default.records_for_rss'))
            ->orderDesc($this->Posts->getAlias() . '.created')
            ->formatResults(fn(ResultSet $results) => $results->map(function (Post $Post): array {
                $description = $text = preg_replace('/\R+/', '', $Post->get('text'));
                //Truncates the description if the "<!-- read-more -->" tag is present or if requested by the configuration
                $strpos = strpos($description, '<!-- read-more -->');
                if (!$strpos && getConfig('default.truncate_to')) {
                    $strpos = getConfig('default.truncate_to');
                    $options = ['exact' => false, 'html' => true];
                }
                $description = strip_tags($strpos !== false ? Text::truncate($description, $strpos, $options ?? ['exact' => true, 'html' => false]) : $description);

                return [
                    'title' => $Post->get('title'),
                    'link' => $Post->get('url'),
                    'guid' => $Post->get('url'),
                    'content:encoded' => $text,
                    'category' => $Post->get('category')->get('title'),
                    'pubDate' => $Post->get('created')->i18nFormat('yyyy-MM-dd HH:mm:ss'),
                ] + compact('description');
            }))
            ->cache('rss');

        $data = [
            'channel' => [
                'title' => getConfigOrFail('main.title'),
                'link' => Router::url('/', true),
                'description' => __d('me_cms', 'Latest posts'),
                'language' => I18n::getLocale(),
            ],
            'items' => $posts->toArray(),
        ];

        $this->set('_serialize', 'data');
        $this->set(compact('data'));
    }

    /**
     * Searches posts
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Controller\Traits\CheckLastSearchTrait::checkLastSearch()
     */
    public function search()
    {
        $pattern = Exceptionist::isString($this->getRequest()->getQuery('p') ?: '');
        $posts = false;

        //Checks if the pattern is at least 4 characters long
        if ($pattern && strlen($pattern) < 4) {
            $this->Flash->alert(__d('me_cms', 'You have to search at least a word of {0} characters', 4));

            return $this->redirect(['action' => $this->getRequest()->getParam('action')]);
        }

        //Checks the last search
        if ($pattern && !$this->checkLastSearch($pattern)) {
            $this->Flash->alert(__d('me_cms', 'You have to wait {0} seconds to perform a new search', getConfigOrFail('security.search_interval')));

            return $this->redirect(['action' => $this->getRequest()->getParam('action')]);
        }

        if ($pattern) {
            $this->paginate['limit'] = getConfigOrFail('default.records_for_searches');

            //Tries to get data from the cache
            $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), $this->paginate['limit'], $this->getQueryPage());
            $posts = Cache::read($cache, $this->Posts->getCacheName());
            $paging = Cache::read($cache . '_paging', $this->Posts->getCacheName());

            //If the data are not available from the cache
            if (!$posts || !$paging) {
                $query = $this->Posts->find('active')
                    ->select(['title', 'slug', 'text', 'created'])
                    ->where(['OR' => [
                        'title LIKE' => '%%' . $pattern . '%%',
                        'subtitle LIKE' => '%%' . $pattern . '%%',
                        'text LIKE' => '%%' . $pattern . '%%',
                    ]])
                    ->orderDesc('created');

                [$posts, $paging] = [$this->paginate($query), $this->getPaging()];

                Cache::writeMany([$cache => $posts, $cache . '_paging' => $paging], $this->Posts->getCacheName());
            //Else, sets the paging parameter
            } else {
                $this->setPaging($paging);
            }
        }

        $this->set(compact('pattern', 'posts'));
    }

    /**
     * Views post
     * @param string $slug Post slug
     * @return void
     * @throws \Tools\Exception\PropertyNotExistsException
     * @uses \MeCms\Model\Table\PostsTable::getRelated()
     */
    public function view(string $slug): void
    {
        $post = $this->Posts->findActiveBySlug($slug)
            ->find('forIndex')
            ->cache('view_' . md5($slug))
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (getConfig('post.related')) {
            [$limit, $images] = array_values(getConfigOrFail('post.related'));
            $this->set('related', $this->Posts->getRelated($post, $limit, $images));
        }
    }

    /**
     * Preview for posts.
     * It uses the `view` template.
     * @param string $slug Post slug
     * @return \Cake\Http\Response
     * @throws \Tools\Exception\PropertyNotExistsException
     */
    public function preview(string $slug): Response
    {
        $post = $this->Posts->findPendingBySlug($slug)
            ->find('forIndex')
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (getConfig('post.related')) {
            [$limit, $images] = array_values(getConfigOrFail('post.related'));
            $this->set('related', $this->Posts->getRelated($post, $limit, $images));
        }

        return $this->render('view');
    }
}
