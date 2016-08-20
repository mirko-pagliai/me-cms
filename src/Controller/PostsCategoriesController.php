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
            ->order(['title' => 'ASC'])
            ->cache('categories_index', $this->PostsCategories->cache)
            ->all();

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
        if ($this->request->query('q')) {
            return $this->redirect([$this->request->query('q')]);
        }

        $page = $this->request->query('page') ? $this->request->query('page') : 1;

        //Sets the cache name
        $cache = sprintf('category_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->PostsCategories->cache));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsCategories->Posts->find('active')
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->contain([
                    'Categories' => function ($q) {
                        return $q->select(['id', 'title', 'slug']);
                    },
                    'Tags' => function ($q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => function ($q) {
                        return $q->select(['first_name', 'last_name']);
                    },
                ])
                ->where(['Categories.slug' => $slug])
                ->order([sprintf('%s.created', $this->PostsCategories->Posts->alias()) => 'DESC']);

            if ($query->isEmpty()) {
                throw new RecordNotFoundException(__d('me_cms', 'Record not found'));
            }

            $posts = $this->paginate($query)->toArray();

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->param('paging'),
            ], $this->PostsCategories->cache);
        //Else, sets the paging parameter
        } else {
            $this->request->params['paging'] = $paging;
        }

        $this->set(am([
            'category' => $posts[0]->category,
        ], compact('posts')));
    }
}
