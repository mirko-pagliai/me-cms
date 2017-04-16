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
namespace MeCms\Controller;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\Network\Exception\ForbiddenException;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\CheckLastSearchTrait;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    use CheckLastSearchTrait;

    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.Controller.Controller.html#_beforeFilter
     * @uses MeCms\Controller\AppController::beforeFilter()
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        $this->Auth->deny('preview');
    }

    /**
     * Lists posts
     * @return void
     */
    public function index()
    {
        $page = $this->request->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('index_limit_%s_page_%s', $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->Posts->find('active')
                ->contain([
                    'Categories' => ['fields' => ['title', 'slug']],
                    'Tags' => function ($q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC']);

            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->Posts->cache);
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set(compact('posts'));
    }

    /**
     * Internal method to get start and end date
     * @param string $date Date as `YYYY/MM/dd`
     * @return array Array with start and end date
     */
    protected function getStartAndEndDate($date)
    {
        $year = $month = $day = null;

        //Sets the start date
        if (in_array($date, ['today', 'yesterday'])) {
            $start = Time::parse($date);
        } else {
            list($year, $month, $day) = array_replace([null, null, null], explode('/', $date));

            $start = Time::now()->setDate($year, $month ?: 1, $day ?: 1);
        }

        $start = $start->setTime(0, 0, 0);

        //Sets the end date
        $end = Time::parse($start);

        if (($year && $month && $day) || in_array($date, ['today', 'yesterday'])) {
            $end = $end->addDay(1);
        } elseif ($year && $month) {
            $end = $end->addMonth(1);
        } else {
            $end = $end->addYear(1);
        }

        return [$start, $end];
    }

    /**
     * List posts for a specific date.
     *
     * Month and day are optional and you can also use special keywords "today"
     *  and "yesterday".
     *
     * Examples:
     * <pre>/posts/2016/06/11</pre>
     * <pre>/posts/2016/06</pre>
     * <pre>/posts/2016</pre>
     * <pre>/posts/today</pre>
     * <pre>/posts/yesterday</pre>
     * @param string $date Date as `YYYY/MM/dd`
     * @return \Cake\Network\Response|null|void
     * @use getStartAndEndDate()
     */
    public function indexByDate($date)
    {
        //Data can be passed as query string, from a widget
        if ($this->request->getQuery('q')) {
            return $this->redirect([$this->request->getQuery('q')]);
        }

        list($start, $end) = $this->getStartAndEndDate($date);

        $page = $this->request->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('index_date_%s_limit_%s_page_%s', md5(serialize([$start, $end])), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->cache
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->Posts->find('active')
                ->contain([
                    'Categories' => ['fields' => ['title', 'slug']],
                    'Tags' => function ($q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->where([
                    sprintf('%s.created >=', $this->Posts->getAlias()) => $start,
                    sprintf('%s.created <', $this->Posts->getAlias()) => $end,
                ])
                ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC']);

            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->Posts->cache);
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set(compact('date', 'posts', 'start'));
    }

    /**
     * Lists posts as RSS
     * @return void
     * @throws \Cake\Network\Exception\ForbiddenException
     */
    public function rss()
    {
        //This method works only for RSS
        if (!$this->RequestHandler->isRss()) {
            throw new ForbiddenException();
        }

        $posts = $this->Posts->find('active')
            ->select(['title', 'slug', 'text', 'created'])
            ->limit(config('default.records_for_rss'))
            ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC'])
            ->cache('rss', $this->Posts->cache)
            ->all();

        $this->set(compact('posts'));
    }

    /**
     * Searches posts
     * @return Cake\Network\Response|null
     * @uses MeCms\Controller\Traits\CheckLastSearchTrait::checkLastSearch()
     */
    public function search()
    {
        $pattern = $this->request->getQuery('p');

        //Checks if the pattern is at least 4 characters long
        if ($pattern && strlen($pattern) < 4) {
            $this->Flash->alert(__d('me_cms', 'You have to search at least a word of {0} characters', 4));

            return $this->redirect([]);
        }

        //Checks the last search
        if ($pattern && !$this->checkLastSearch($pattern)) {
            $this->Flash->alert(__d(
                'me_cms',
                'You have to wait {0} seconds to perform a new search',
                config('security.search_interval')
            ));

            return $this->redirect([]);
        }

        if ($pattern) {
            $this->paginate['limit'] = config('default.records_for_searches');

            $page = $this->request->getQuery('page', 1);

            //Sets the cache name
            $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), $this->paginate['limit'], $page);

            //Tries to get data from the cache
            list($posts, $paging) = array_values(Cache::readMany(
                [$cache, sprintf('%s_paging', $cache)],
                $this->Posts->cache
            ));

            //If the data are not available from the cache
            if (empty($posts) || empty($paging)) {
                $query = $this->Posts->find('active')
                    ->select(['title', 'slug', 'text', 'created'])
                    ->where(['OR' => [
                        'title LIKE' => sprintf('%%%s%%', $pattern),
                        'subtitle LIKE' => sprintf('%%%s%%', $pattern),
                        'text LIKE' => sprintf('%%%s%%', $pattern),
                    ]])
                    ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC']);

                $posts = $this->paginate($query);

                //Writes on cache
                Cache::writeMany([
                    $cache => $posts,
                    sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
                ], $this->Posts->cache);
            //Else, sets the paging parameter
            } else {
                $this->request = $this->request->withParam('paging', $paging);
            }

            $this->set(compact('posts'));
        }

        $this->set(compact('pattern'));
    }

    /**
     * Views post
     * @param string $slug Post slug
     * @return void
     * @uses MeCms\Model\Table\PostsTable::getRelated()
     */
    public function view($slug = null)
    {
        $post = $this->Posts->find('active')
            ->contain([
                'Categories' => ['fields' => ['title', 'slug']],
                'Tags' => function ($q) {
                    return $q->order(['tag' => 'ASC']);
                },
                'Users' => ['fields' => ['first_name', 'last_name']],
            ])
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'preview', 'active', 'created', 'modified'])
            ->where([sprintf('%s.slug', $this->Posts->getAlias()) => $slug])
            ->cache(sprintf('view_%s', md5($slug)), $this->Posts->cache)
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (config('post.related') && config('post.related.limit')) {
            $related = $this->Posts->getRelated($post, config('post.related.limit'), config('post.related.images'));
            $this->set(compact('related'));
        }
    }

    /**
     * Preview for posts.
     * It uses the `view` template.
     * @param string $slug Post slug
     * @return \Cake\Network\Response
     * @uses MeCms\Model\Table\PostsTable::getRelated()
     */
    public function preview($slug = null)
    {
        $post = $this->Posts->find('pending')
            ->contain([
                'Categories' => ['fields' => ['title', 'slug']],
                'Tags' => function ($q) {
                    return $q->order(['tag' => 'ASC']);
                },
                'Users' => ['fields' => ['first_name', 'last_name']],
            ])
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
            ->where([sprintf('%s.slug', $this->Posts->getAlias()) => $slug])
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (config('post.related') && config('post.related.limit')) {
            $this->set('related', $this->Posts->getRelated($post, config('post.related.limit'), config('post.related.images')));
        }

        $this->render('view');
    }
}
