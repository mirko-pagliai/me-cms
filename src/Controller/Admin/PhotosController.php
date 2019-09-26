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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\InternalErrorException;
use MeCms\Controller\Admin\AppController;

/**
 * Photos controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $Albums
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
     * @uses \MeCms\Model\Table\PhotosAlbums::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        //Gets albums
        $albums = $this->Albums->getList();

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
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can delete photos
        return $this->getRequest()->isDelete() ? $this->Auth->isGroup(['admin', 'manager']) : true;
    }

    /**
     * Lists photos.
     *
     * This action can use the `index_as_grid` template.
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Model\Table\PhotosTable::queryFromFilter()
     */
    public function index()
    {
        //The "render" type can set by query or by cookies
        $render = $this->getRequest()->getQuery('render', $this->getRequest()->getCookie('render-photos'));

        $query = $this->Photos->find()->contain(['Albums' => ['fields' => ['id', 'slug', 'title']]]);

        $this->paginate['order'] = ['Photos.created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.photos');
            $this->viewBuilder()->setTemplate('index_as_grid');
        }

        $this->set('photos', $this->paginate($this->Photos->queryFromFilter($query, $this->getRequest()->getQueryParams())));
        $this->set('title', I18N_PHOTOS);

        if ($render) {
            $cookie = (new Cookie('render-photos', $render))->withNeverExpire();
            $this->setResponse($this->getResponse()->withCookie($cookie));
        }
    }

    /**
     * Uploads photos
     * @return null
     * @throws \Cake\Http\Exception\InternalErrorException
     * @uses \MeCms\Controller\Admin\AppController::setUploadError()
     * @uses \MeTools\Controller\Component\UploaderComponent
     */
    public function upload()
    {
        $album = $this->getRequest()->getQuery('album');
        $albums = $this->viewVars['albums']->toArray();

        //If there's only one available album
        if (!$album && count($albums) < 2) {
            $album = array_key_first($albums);
            $this->setRequest($this->getRequest()->withQueryParams(compact('album')));
        }

        if ($this->getRequest()->getData('file')) {
            is_true_or_fail($album, __d('me_cms', 'Missing ID'), InternalErrorException::class);

            $uploaded = $this->Uploader->set($this->getRequest()->getData('file'))
                ->mimetype('image')
                ->save(PHOTOS . $album);

            if (!$uploaded) {
                return $this->setUploadError($this->Uploader->getError());
            }

            $entity = $this->Photos->newEntity([
                'album_id' => $album,
                'filename' => basename($uploaded),
            ]);

            if ($entity->getErrors()) {
                return $this->setUploadError(array_value_first_recursive($entity->getErrors()));
            }

            if (!$this->Photos->save($entity)) {
                $this->setUploadError(I18N_OPERATION_NOT_OK);
            }
        }
    }

    /**
     * Edits photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id)
    {
        $photo = $this->Photos->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $photo = $this->Photos->patchEntity($photo, $this->getRequest()->getData());

            if ($this->Photos->save($photo)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index', $photo->get('album_id')]);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('photo'));
    }

    /**
     * Downloads photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response
     */
    public function download($id)
    {
        return $this->getResponse()->withFile($this->Photos->get($id)->get('path'), ['download' => true]);
    }

    /**
     * Deletes photo
     * @param string $id Photo ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $photo = $this->Photos->get($id);
        $this->Photos->deleteOrFail($photo);
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index', $photo->get('album_id')]);
    }
}
