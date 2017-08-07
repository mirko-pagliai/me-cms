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
            ->cache('categories_index', $this->PostsCategories->cache);

        $this->set(compact('categories'));
    }

    /**
     * Lists posts for a category
     * @param string $slug Category slug
     * @return \Cake\Network\Response|null|void
     * @throws RecordNotFoundException
     */
    public function view($slug = null)
    {
        //The category can be passed as query string, from a widget
        if ($this->request->getQuery('q')) {
            return $this->redirect([$this->request->getQuery('q')]);
        }

        $page = $this->request->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('category_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsCategories->cache
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsCategories->Posts->find('active')
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->contain([
                    'Categories' => ['fields' => ['id', 'title', 'slug']],
                    'Tags' => function ($q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->where([sprintf('%s.slug', $this->PostsCategories->getAlias()) => $slug])
                ->order([sprintf('%s.created', $this->PostsCategories->Posts->getAlias()) => 'DESC']);

            if ($query->isEmpty()) {
                throw new RecordNotFoundException(I18N_NOT_FOUND);
            }

            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->PostsCategories->cache);
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set('category', $posts->extract('category')->first());
        $this->set(compact('posts'));
    }
}
