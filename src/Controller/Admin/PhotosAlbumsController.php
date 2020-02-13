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

namespace MeCms\Controller\Admin;

use Cake\Http\Response;
use MeCms\Controller\Admin\AppController;

/**
 * PhotosAlbums controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $PhotosAlbums
 */
class PhotosAlbumsController extends AppController
{
    /**
     * Check if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins and managers can delete albums
        return !$this->getRequest()->isDelete() ?: $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists albums
     * @return void
     */
    public function index(): void
    {
        $this->paginate['order'] = ['created' => 'DESC'];

        $albums = $this->paginate($this->PhotosAlbums->find());

        $this->set(compact('albums'));
    }

    /**
     * Adds photos album
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $album = $this->PhotosAlbums->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->getRequest()->getData());

            if ($this->PhotosAlbums->save($album)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('album'));
    }

    /**
     * Edits photos album
     * @param string $id Photos Album ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $album = $this->PhotosAlbums->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->getRequest()->getData());

            if ($this->PhotosAlbums->save($album)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('album'));
    }

    /**
     * Deletes photos album
     * @param string $id Photos Album ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        //Before deleting, it checks if the album has some photos
        $album = $this->PhotosAlbums->get($id);
        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        if (!$album->get('photo_count')) {
            $this->PhotosAlbums->deleteOrFail($album);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect($this->referer(['action' => 'index']));
    }
}
