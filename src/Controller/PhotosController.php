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
                ->contain([$this->Photos->Albums->getAlias() => ['fields' => ['slug']]])
                ->extract('album.slug')
                ->first();

            return $this->redirect(compact('id', 'slug'), 301);
        }

        $photo = $this->Photos->find('active')
            ->select(['id', 'album_id', 'filename', 'active', 'modified'])
            ->contain([$this->Photos->Albums->getAlias() => function ($q) {
                return $q->select(['id', 'title', 'slug']);
            }])
            ->where([sprintf('%s.id', $this->Photos->getAlias()) => $id])
            ->cache(sprintf('view_%s', md5($id)), $this->Photos->cache)
            ->firstOrFail();

        $this->set(compact('photo'));
    }

    /**
     * Preview for photos.
     * It uses the `view` template.
     * @param string $id Photo ID
     * @return \Cake\Network\Response
     */
    public function preview($id = null)
    {
        $photo = $this->Photos->find('pending')
            ->select(['id', 'album_id', 'filename'])
            ->contain([$this->Photos->Albums->getAlias() => function ($q) {
                return $q->select(['id', 'title', 'slug']);
            }])
            ->where([sprintf('%s.id', $this->Photos->getAlias()) => $id])
            ->firstOrFail();

        $this->set(compact('photo'));

        $this->render('view');
    }
}
