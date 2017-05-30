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
use Cake\Utility\Text;
use MeCms\Controller\AppController;

/**
 * PostsTags controller
 * @property \MeCms\Model\Table\PostsTagsTable $PostsTags
 */
class PostsTagsController extends AppController
{
    /**
     * Lists posts tags
     * @return void
     */
    public function index()
    {
        $page = $this->request->getQuery('page', 1);

        $this->paginate['order'] = ['tag' => 'ASC'];

        //Limit X4
        $this->paginate['limit'] = $this->paginate['maxLimit'] = $this->paginate['limit'] * 4;

        //Sets the cache name
        $cache = sprintf('tags_limit_%s_page_%s', $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($tags, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->cache
        ));

        //If the data are not available from the cache
        if (empty($tags) || empty($paging)) {
            $query = $this->PostsTags->Tags->find('active');

            $tags = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $tags,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->PostsTags->cache);
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set(compact('tags'));
    }

    /**
     * Lists posts for a tag
     * @param string $slug Tag slug
     * @return \Cake\Network\Response|null|void
     */
    public function view($slug = null)
    {
        //Data can be passed as query string, from a widget
        if ($this->request->getQuery('q')) {
            return $this->redirect([$this->request->getQuery('q')]);
        }

        $slug = Text::slug($slug, ['replacement' => ' ']);

        $tag = $this->PostsTags->Tags->find('active')
            ->where(['tag' => $slug])
            ->cache((sprintf('tag_%s', md5($slug))), $this->PostsTags->cache)
            ->firstOrFail();

        $page = $this->request->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('tag_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->cache
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsTags->Posts->find('active')
                ->contain([
                    'Categories' => ['fields' => ['title', 'slug']],
                    'Tags' => function ($q) {
                        return $q->order(['tag' => 'ASC']);
                    },
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->matching($this->PostsTags->Tags->getAlias(), function ($q) use ($slug) {
                    return $q->where(['tag' => $slug]);
                })
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->order([sprintf('%s.created', $this->PostsTags->Posts->getAlias()) => 'DESC']);

            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->PostsTags->cache);
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set(compact('posts', 'tag'));
    }
}
