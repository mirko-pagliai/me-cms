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
use MeCms\Controller\AppController;
use MeCms\Model\Entity\PhotosAlbum;
use MeCms\ORM\Query;

/**
 * PhotosAlbums controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $PhotosAlbums
 * @property \MeCms\Model\Table\PhotosTable $Photos
 */
class PhotosAlbumsController extends AppController
{
    /**
     * Lists albums
     * @return \Cake\Network\Response|null|void
     */
    public function index()
    {
        $albums = $this->PhotosAlbums->find('active')
            ->select(['id'])
            ->contain($this->Photos->getAlias(), function (Query $query) {
                return $query->find('active')->select(['id', 'album_id', 'filename']);
            })
            ->orderDesc(sprintf('%s.created', $this->PhotosAlbums->getAlias()))
            ->enableAutoFields(true)
            ->cache('albums_index');

        //If there is only one record, redirects
        if ($albums->count() === 1) {
            return $this->redirect(['_name' => 'album', $albums->first()->get('slug')]);
        }

        //Album photos are randomly ordered
        $albums = $albums->all()->map(function (PhotosAlbum $album) {
            $photos = $album->get('photos');
            shuffle($photos);

            return $album->set(compact('photos'));
        });

        $this->set(compact('albums'));
    }

    /**
     * Views album
     * @param string $slug Album slug
     * @return \Cake\Network\Response|null|void
     */
    public function view($slug)
    {
        //Data can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        //Gets album ID and title
        $album = $this->PhotosAlbums->findActiveBySlug($slug)
            ->select(['id', 'title'])
            ->cache('album_' . md5($slug))
            ->firstOrFail();

        $page = $this->getRequest()->getQuery('page', 1);
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('default.photos');

        //Sets the cache name
        $cache = sprintf('album_%s_limit_%s_page_%s', md5($slug), $this->paginate['limit'], $page);

        //Tries to get data from the cache
        list($photos, $paging) = array_values(Cache::readMany(
            [$cache, sprintf('%s_paging', $cache)],
            $this->PhotosAlbums->getCacheName()
        ));

        //If the data are not available from the cache
        if (empty($photos) || empty($paging)) {
            $query = $this->Photos->findActiveByAlbumId($album->get('id'))
                ->order([
                    sprintf('%s.created', $this->Photos->getAlias()) => 'DESC',
                    sprintf('%s.id', $this->Photos->getAlias()) => 'DESC',
                ]);

            list($photos, $paging) = [$this->paginate($query), $this->getPaging()];

            Cache::writeMany([
                $cache => $photos,
                sprintf('%s_paging', $cache) => $paging,
            ], $this->PhotosAlbums->getCacheName());
        //Else, sets the paging parameter
        } else {
            $this->setRequest($this->getRequest()->withParam('paging', $paging));
        }

        $this->set(compact('album', 'photos'));
    }
}
