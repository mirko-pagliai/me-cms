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
            $photo = $this->Photos->findById($id)
                ->contain([$this->Photos->Albums->getAlias() => ['fields' => ['slug']]])
                ->firstOrFail();

            return $this->redirect(compact('id') + ['slug' => $photo->get('album')->get('slug')], 301);
        }

        $photo = $this->Photos->findActiveById($id)
            ->contain([$this->Photos->Albums->getAlias() => ['fields' => ['id', 'title', 'slug']]])
            ->cache('view_' . md5($id))
            ->firstOrFail();

        $this->set(compact('photo'));
    }

    /**
     * Preview for photos.
     * It uses the `view` template.
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function preview($id)
    {
        $photo = $this->Photos->findPendingById($id)
            ->select(['id', 'album_id', 'filename'])
            ->contain([$this->Photos->Albums->getAlias() => ['fields' => ['id', 'title', 'slug']]])
            ->firstOrFail();

        $this->set(compact('photo'));
        $this->render('view');
    }
}
