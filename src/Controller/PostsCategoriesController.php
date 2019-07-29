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
use Cake\Datasource\Exception\RecordNotFoundException;
use MeCms\Controller\AppController;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController
{
    /**
     * Lists posts categories
     * @return void
     */
    public function index()
    {
        $categories = $this->PostsCategories->find('active')
            ->select(['title', 'slug'])
            ->order([sprintf('%s.title', $this->PostsCategories->getAlias()) => 'ASC'])
            ->cache('categories_index', $this->PostsCategories->getCacheName());

        $this->set(compact('categories'));
    }

    /**
     * Lists posts for a category
     * @param string $slug Category slug
     * @return \Cake\Network\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function view($slug)
    {
        //The category can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        $page = $this->getRequest()->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('category_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsCategories->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsCategories->Posts->find('active')
                ->find('forIndex')
                ->where([sprintf('%s.slug', $this->PostsCategories->getAlias()) => $slug]);

            is_true_or_fail(!$query->isEmpty(), I18N_NOT_FOUND, RecordNotFoundException::class);

            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->getRequest()->getParam('paging'),
            ], $this->PostsCategories->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setRequest($this->getRequest()->withParam('paging', $paging));
        }

        $this->set('category', $posts->extract('category')->first());
        $this->set(compact('posts'));
    }
}
