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
 * PostsTags controller
 * @property \MeCms\Model\Table\PostsTagsTable $PostsTags
 */
class PostsTagsController extends AppController
{
    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can edit tags
        if ($this->request->isEdit()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        return true;
    }

    /**
     * Lists tags
     * @return void
     * @uses MeCms\Model\Table\Tags::queryFromFilter()
     */
    public function index()
    {
        $query = $this->PostsTags->Tags->find()
            ->matching($this->PostsTags->Posts->alias());

        $this->paginate['order'] = ['tag' => 'ASC'];

        //Limit X4
        $this->paginate['limit'] = $this->paginate['maxLimit'] = $this->paginate['limit'] * 4;

        $tags = $this->paginate($this->PostsTags->Tags->queryFromFilter($query, $this->request->getQuery()));

        $this->set(compact('tags'));
    }

    /**
     * Edits tag
     * @param string $id Tag ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $tag = $this->PostsTags->Tags->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->PostsTags->Tags->patchEntity($tag, $this->request->getData());

            if ($this->PostsTags->Tags->save($tag)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('tag'));
    }
}
