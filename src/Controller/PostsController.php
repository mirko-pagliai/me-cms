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
namespace MeCms\Controller;

use Cake\Cache\Cache;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\Query;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\CheckLastSearchTrait;
use MeCms\Controller\Traits\GetStartAndEndDateTrait;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    use CheckLastSearchTrait;
    use GetStartAndEndDateTrait;

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
                    'Tags' => function (Query $q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => ['fields' => ['id', 'first_name', 'last_name']],
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
     * Lists posts for a specific date.
     *
     * Month and day are optional and you can also use special keywords `today`
     *  or `yesterday`.
     *
     * Examples:
     * <pre>/posts/2016/06/11</pre>
     * <pre>/posts/2016/06</pre>
     * <pre>/posts/2016</pre>
     * <pre>/posts/today</pre>
     * <pre>/posts/yesterday</pre>
     * @param string $date Date as `today`, `yesterday`, `YYYY/MM/dd`,
     *  `YYYY/MM` or `YYYY`
     * @return \Cake\Network\Response|null|void
     * @use \MeCms\Controller\Traits\GetStartAndEndDateTrait\getStartAndEndDate()
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
                    'Tags' => function (Query $q) {
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
            throw new ForbiddenException;
        }

        $posts = $this->Posts->find('active')
            ->select(['title', 'slug', 'text', 'created'])
            ->limit(getConfigOrFail('default.records_for_rss'))
            ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC'])
            ->cache('rss', $this->Posts->cache);

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
                getConfigOrFail('security.search_interval')
            ));

            return $this->redirect([]);
        }

        if ($pattern) {
            $this->paginate['limit'] = getConfigOrFail('default.records_for_searches');

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
                'Tags' => function (Query $q) {
                    return $q->order(['tag' => 'ASC']);
                },
                'Users' => ['fields' => ['id', 'first_name', 'last_name']],
            ])
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'preview', 'active', 'created', 'modified'])
            ->where([sprintf('%s.slug', $this->Posts->getAlias()) => $slug])
            ->cache(sprintf('view_%s', md5($slug)), $this->Posts->cache)
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (getConfig('post.related')) {
            $related = $this->Posts->getRelated($post, getConfigOrFail('post.related.limit'), getConfig('post.related.images'));
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
                'Tags' => function (Query $q) {
                    return $q->order(['tag' => 'ASC']);
                },
                'Users' => ['fields' => ['id', 'first_name', 'last_name']],
            ])
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
            ->where([sprintf('%s.slug', $this->Posts->getAlias()) => $slug])
            ->firstOrFail();

        $this->set(compact('post'));

        //Gets related posts
        if (getConfig('post.related')) {
            $related = $this->Posts->getRelated($post, getConfigOrFail('post.related.limit'), getConfig('post.related.images'));
            $this->set(compact('related'));
        }

        $this->render('view');
    }
}
