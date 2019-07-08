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

use MeCms\Controller\AppController;

/**
 * BannersPositions controller
 * @property \MeCms\Model\Table\BannersPositionsTable $BannersPositions
 */
class BannersPositionsController extends AppController
{
    /**
     * Checks if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Lists positions
     * @return void
     */
    public function index()
    {
        $this->paginate['order'] = ['title' => 'ASC'];

        $positions = $this->paginate($this->BannersPositions->find());

        $this->set(compact('positions'));
    }

    /**
     * Adds banners position
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $position = $this->BannersPositions->newEntity();

        if ($this->request->is('post')) {
            $position = $this->BannersPositions->patchEntity($position, $this->request->getData());

            if ($this->BannersPositions->save($position)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('position'));
    }

    /**
     * Edits banners position
     * @param string $id Banners Position ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $position = $this->BannersPositions->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $position = $this->BannersPositions->patchEntity($position, $this->request->getData());

            if ($this->BannersPositions->save($position)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('position'));
    }

    /**
     * Deletes banners position
     * @param string $id Banners Position ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $position = $this->BannersPositions->get($id);

        //Before deleting, it checks if the position has some banners
        if (!$position->banner_count) {
            $this->BannersPositions->deleteOrFail($position);
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
