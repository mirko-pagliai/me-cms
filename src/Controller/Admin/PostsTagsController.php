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

/**
 * PostsTags controller
 * @property \MeCms\Model\Table\PostsTagsTable $PostsTags
 * @property \MeCms\Model\Table\TagsTable $Tags
 */
class PostsTagsController extends AppController
{
    /**
     * Check if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins and managers can edit tags
        return !$this->getRequest()->is('edit') || $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists tags
     * @return void
     */
    public function index(): void
    {
        $query = $this->Tags->find()->matching('Posts');

        $this->paginate['order'] = ['tag' => 'ASC'];

        //Limit X6
        $this->paginate['limit'] = $this->paginate['maxLimit'] = $this->paginate['limit'] * 6;

        $tags = $this->paginate($this->Tags->queryFromFilter($query, $this->getRequest()->getQueryParams()));

        $this->set(compact('tags'));
    }

    /**
     * Edits tag
     * @param string $id Tag ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $tag = $this->Tags->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->getRequest()->getData());

            if ($this->Tags->save($tag)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('tag'));
    }
}
