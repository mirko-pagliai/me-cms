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

use Cake\ORM\Query;
use MeCms\Controller\AppController;

/**
 * Photos controller
 * @property \MeCms\Model\Table\PhotosTable $Photos
 */
class PhotosController extends AppController
{
    /**
     * Views a photo
     * @param string $slug Album slug
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function view($slug = null, $id = null)
    {
        //This allows backward compatibility for URLs like `/photo/11`
        if (empty($slug)) {
            $slug = $this->Photos->findById($id)
                ->contain($this->Photos->Albums->getAlias(), function (Query $q) {
                    return $q->select(['slug']);
                })
                ->extract('album.slug')
                ->first();

            return $this->redirect(compact('id', 'slug'), 301);
        }

        $photo = $this->Photos->find('active')
            ->select(['id', 'album_id', 'filename', 'active', 'modified'])
            ->contain($this->Photos->Albums->getAlias(), function (Query $q) {
                return $q->select(['id', 'title', 'slug']);
            })
            ->where([sprintf('%s.id', $this->Photos->getAlias()) => $id])
            ->cache(sprintf('view_%s', md5($id)), $this->Photos->cache)
            ->firstOrFail();

        $this->set(compact('photo'));
    }

    /**
     * Preview for photos.
     * It uses the `view` template.
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function preview($id = null)
    {
        $photo = $this->Photos->find('pending')
            ->select(['id', 'album_id', 'filename'])
            ->contain($this->Photos->Albums->getAlias(), function (Query $q) {
                return $q->select(['id', 'title', 'slug']);
            })
            ->where([sprintf('%s.id', $this->Photos->getAlias()) => $id])
            ->firstOrFail();

        $this->set(compact('photo'));

        $this->render('view');
    }
}
