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
use Cake\ORM\Query;
use Cake\Utility\Text;

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
    public function index(): void
    {
        $this->paginate['order'] = ['tag' => 'ASC'];

        //Limit X4
        $this->paginate['limit'] = $this->paginate['maxLimit'] = $this->paginate['limit'] * 4;

        //Sets the cache name
        $cache = sprintf(
            'tags_limit_%s_page_%s',
            $this->paginate['limit'],
            trim((string)$this->getRequest()->getQuery('page', 1), '/')
        );

        //Tries to get data from the cache
        [$tags, $paging] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($tags) || empty($paging)) {
            $query = $this->PostsTags->Tags->find('active');

            [$tags, $paging] = [$this->paginate($query), $this->getPaging()];

            Cache::writeMany([
                $cache => $tags,
                sprintf('%s_paging', $cache) => $paging,
            ], $this->PostsTags->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setPaging($paging);
        }

        $this->set(compact('tags'));
    }

    /**
     * Lists posts for a tag
     * @param string $slug Tag slug
     * @return \Cake\Http\Response|null|void
     */
    public function view(string $slug)
    {
        //Data can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        $slug = Text::slug($slug, ['replacement' => ' ']);

        $tag = $this->PostsTags->Tags->findActiveByTag($slug)
            ->cache('tag_' . md5($slug))
            ->firstOrFail();

        //Sets the cache name
        $cache = sprintf(
            'tag_%s_limit_%s_page_%s',
            md5($slug),
            $this->paginate['limit'],
            trim((string)$this->getRequest()->getQuery('page', 1), '/')
        );

        //Tries to get data from the cache
        [$posts, $paging] = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsTags->Posts->find('active')
                ->find('forIndex')
                ->innerJoinWith($this->PostsTags->Tags->getAlias(), fn(Query $query): Query => $query->where(['tag' => $slug]));

            [$posts, $paging] = [$this->paginate($query), $this->getPaging()];

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $paging,
            ], $this->PostsTags->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setPaging($paging);
        }

        $this->set(compact('posts', 'tag'));
    }
}
