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
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\CheckLastSearchTrait;
use MeCms\Controller\Traits\GetStartAndEndDateTrait;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    use CheckLastSearchTrait, GetStartAndEndDateTrait;

    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Network\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        $this->Auth->deny('preview');
    }

    /**
     * Lists posts
     * @return void
     */
    public function index(): void
    {
        $page = $this->getRequest()->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('index_limit_%s_page_%s', $this->paginate['limit'], $page);

        //Tries to get data from the cache
        [$posts, $paging] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $posts = $this->paginate($this->Posts->find('active')->find('forIndex'));

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->getRequest()->getParam('paging'),
            ], $this->Posts->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setRequest($this->getRequest()->withParam('paging', $paging));
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
     * @return \Cake\Http\Response|null|void
     * @use \MeCms\Controller\Traits\GetStartAndEndDateTrait::getStartAndEndDate()
     */
    public function indexByDate(string $date)
    {
        //Data can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        [$start, $end] = $this->getStartAndEndDate($date);

        $page = $this->getRequest()->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf(
            'index_date_%s_limit_%s_page_%s',
            md5(serialize([$start, $end])),
            $this->paginate['limit'],
            $page
        );

        //Tries to get data from the cache
        [$posts, $paging] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->Posts->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->Posts->find('active')
                ->find('forIndex')
                ->where([
                    sprintf('%s.created >=', $this->Posts->getAlias()) => $start,
                    sprintf('%s.created <', $this->Posts->getAlias()) => $end,
                ]);
            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->getRequest()->getParam('paging'),
            ], $this->Posts->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setRequest($this->getRequest()->withParam('paging', $paging));
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
        is_true_or_fail($this->RequestHandler->prefers('rss'), ForbiddenException::class);

        $posts = $this->Posts->find('active')
            ->select(['title', 'preview', 'slug', 'text', 'created'])
            ->limit(getConfigOrFail('default.records_for_rss'))
            ->orderDesc('created')
            ->cache('rss');

        $this->set(compact('posts'));
    }

    /**
     * Searches posts
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Controller\Traits\CheckLastSearchTrait::checkLastSearch()
     */
    public function search()
    {
        $pattern = $this->getRequest()->getQuery('p');
        $posts = false;

        //Checks if the pattern is at least 4 characters long
        if ($pattern && strlen($pattern) < 4) {
            $this->Flash->alert(__d('me_cms', 'You have to search at least a word of {0} characters', 4));

            return $this->redirect(['action' => $this->getRequest()->getParam('action')]);
        }

        //Checks the last search
        if ($pattern && !$this->checkLastSearch($pattern)) {
            $this->Flash->alert(__d(
                'me_cms',
                'You have to wait {0} seconds to perform a new search',
                getConfigOrFail('security.search_interval')
            ));

            return $this->redirect(['action' => $this->getRequest()->getParam('action')]);
        }

        if ($pattern) {
            $this->paginate['limit'] = getConfigOrFail('default.records_for_searches');

            $page = $this->getRequest()->getQuery('page', 1);

            //Sets the cache name
            $cache = sprintf('search_%s_limit_%s_page_%s', md5($pattern), $this->paginate['limit'], $page);

            //Tries to get data from the cache
            [$posts, $paging] = array_values(Cache::readMany(
                [$cache, sprintf('%s_paging', $cache)],
                $this->Posts->getCacheName()
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
                    ->orderDesc('created');

                $posts = $this->paginate($query);

                //Writes on cache
                Cache::writeMany([
                    $cache => $posts,
                    sprintf('%s_paging', $cache) => $this->getRequest()->getParam('paging'),
                ], $this->Posts->getCacheName());
            //Else, sets the paging parameter
            } else {
                $this->setRequest($this->getRequest()->withParam('paging', $paging));
            }
        }

        $this->set(compact('pattern', 'posts'));
    }

    /**
     * Views post
     * @param string $slug Post slug
     * @return void
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
     * @uses \MeCms\Model\Table\PostsTable::getRelated()
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
