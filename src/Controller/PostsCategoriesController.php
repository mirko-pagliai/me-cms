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
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsCategoriesController extends AppController
{
    /**
     * Lists posts categories
     * @return void
     */
    public function index(): void
    {
        $categories = $this->PostsCategories->find('active')
            ->select(['title', 'slug'])
            ->orderAsc(sprintf('%s.title', $this->PostsCategories->getAlias()))
            ->cache('categories_index')
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Lists posts for a category
     * @param string $slug Category slug
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function view(string $slug)
    {
        //The category can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        //Sets the cache name
        $cache = sprintf(
            'category_%s_limit_%s_page_%s',
            md5($slug),
            $this->paginate['limit'],
            trim((string)$this->getRequest()->getQuery('page', 1), '/')
        );

        //Tries to get data from the cache
        [$posts, $paging] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsCategories->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->Posts->find('active')
                ->find('forIndex')
                ->innerJoinWith($this->PostsCategories->getAlias(), fn(Query $query): Query => $query->where([sprintf('%s.slug', $this->PostsCategories->getAlias()) => $slug]));

            if ($query->all()->isEmpty()) {
                throw new RecordNotFoundException(I18N_NOT_FOUND);
            }

            [$posts, $paging] = [$this->paginate($query), $this->getPaging()];

            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $paging,
            ], $this->PostsCategories->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setPaging($paging);
        }

        $this->set('category', $posts->extract('category')->first());
        $this->set(compact('posts'));
    }
}
