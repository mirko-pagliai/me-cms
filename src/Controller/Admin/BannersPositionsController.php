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
 * BannersPositions controller
 * @property \MeCms\Model\Table\BannersPositionsTable $BannersPositions
 */
class BannersPositionsController extends AppController
{
    /**
     * Checks if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Lists positions
     * @return void
     */
    public function index(): void
    {
        $this->paginate['order'] = ['title' => 'ASC'];

        $positions = $this->paginate($this->BannersPositions->find());

        $this->set(compact('positions'));
    }

    /**
     * Adds banners position
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $position = $this->BannersPositions->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $position = $this->BannersPositions->patchEntity($position, $this->getRequest()->getData());

            if ($this->BannersPositions->save($position)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('position'));
    }

    /**
     * Edits banners position
     * @param string $id Banners Position ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $position = $this->BannersPositions->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $position = $this->BannersPositions->patchEntity($position, $this->getRequest()->getData());

            if ($this->BannersPositions->save($position)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('position'));
    }

    /**
     * Deletes banners position
     * @param string $id Banners Position ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $position = $this->BannersPositions->get($id);

        //Before deleting, it checks if the position has some banners
        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        if (!$position->get('banner_count')) {
            $this->BannersPositions->deleteOrFail($position);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect($this->referer(['action' => 'index']));
    }
}
