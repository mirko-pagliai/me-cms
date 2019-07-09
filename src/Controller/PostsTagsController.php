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
use Cake\ORM\Query;
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
            $this->PostsTags->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($tags) || empty($paging)) {
            $query = $this->PostsTags->Tags->find('active');
            $tags = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $tags,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->PostsTags->getCacheName());
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
    public function view($slug)
    {
        //Data can be passed as query string, from a widget
        if ($this->request->getQuery('q')) {
            return $this->redirect([$this->request->getQuery('q')]);
        }

        $slug = Text::slug($slug, ['replacement' => ' ']);

        $tag = $this->PostsTags->Tags->findActiveByTag($slug)
            ->cache(sprintf('tag_%s', md5($slug)), $this->PostsTags->getCacheName())
            ->firstOrFail();

        $page = $this->request->getQuery('page', 1);

        //Sets the cache name
        $cache = sprintf('tag_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($posts, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PostsTags->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($posts) || empty($paging)) {
            $query = $this->PostsTags->Posts->find('active')
                ->find('forIndex')
                ->matching($this->PostsTags->Tags->getAlias(), function (Query $q) use ($slug) {
                    return $q->where(['tag' => $slug]);
                });
            $posts = $this->paginate($query);

            //Writes on cache
            Cache::writeMany([
                $cache => $posts,
                sprintf('%s_paging', $cache) => $this->request->getParam('paging'),
            ], $this->PostsTags->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->request = $this->request->withParam('paging', $paging);
        }

        $this->set(compact('posts', 'tag'));
    }
}
