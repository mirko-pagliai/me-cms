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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;

/**
 * Photos controller
 * @property \MeCms\Model\Table\PhotosTable $Photos
 */
class PhotosController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *   each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PhotosAlbums::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        //Gets albums
        $albums = $this->Photos->Albums->getList();

        if ($albums->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create an album'));

            return $this->redirect(['controller' => 'PhotosAlbums', 'action' => 'index']);
        }

        $this->set(compact('albums'));
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can delete photos
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        return true;
    }

    /**
     * Lists photos.
     *
     * This action can use the `index_as_grid` template.
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Model\Table\PhotosTable::queryFromFilter()
     */
    public function index()
    {
        $render = $this->request->getQuery('render');

        //The "render" type can also be set via cookies, if it's not set by query
        if (!$render && $this->Cookie->check('renderPhotos')) {
            $render = $this->Cookie->read('renderPhotos');
        }

        $query = $this->Photos->find()->contain(['Albums' => ['fields' => ['id', 'slug', 'title']]]);

        $this->paginate['order'] = ['Photos.created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = config('admin.photos');
        }

        $this->set('photos', $this->paginate($this->Photos->queryFromFilter($query, $this->request->getQuery())));

        if ($render) {
            $this->Cookie->write('renderPhotos', $render);

            if ($render === 'grid') {
                $this->render('index_as_grid');
            }
        }
    }

    /**
     * Uploads photos
     * @return void
     * @uses MeCms\Controller\AppController::setUploadError()
     * @uses MeTools\Controller\Component\UploaderComponent
     */
    public function upload()
    {
        $album = $this->request->getQuery('album');
        $albums = $this->viewVars['albums']->toArray();

        //If there's only one available album
        if (!$album && count($albums) < 2) {
            $album = collection(array_keys($albums))->first();
            $this->request = $this->request->withQueryParams(compact('album'));
        }

        if ($this->request->getData('file')) {
            if (!$album) {
                throw new InternalErrorException(__d('me_cms', 'Missing album ID'));
            }

            $uploaded = $this->Uploader->set($this->request->getData('file'))
                ->mimetype('image')
                ->save(PHOTOS . $album);

            if (!$uploaded) {
                $this->setUploadError($this->Uploader->error());

                return;
            }

            $saved = $this->Photos->save($this->Photos->newEntity([
                'album_id' => $album,
                'filename' => basename($uploaded),
            ]));

            if (!$saved) {
                $this->setUploadError(__d('me_cms', 'The photo could not be saved'));
            }
        }
    }

    /**
     * Edits photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $photo = $this->Photos->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $photo = $this->Photos->patchEntity($photo, $this->request->getData());

            if ($this->Photos->save($photo)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index', $photo->album_id]);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('photo'));
    }

    /**
     * Downloads photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response
     */
    public function download($id = null)
    {
        $file = $this->Photos->get($id)->path;

        return $this->response->withFile($file, ['download' => true]);
    }

    /**
     * Deletes photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $photo = $this->Photos->get($id);

        $this->Photos->deleteOrFail($photo);

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index', $photo->album_id]);
    }
}
