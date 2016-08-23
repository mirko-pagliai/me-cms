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

use MeCms\Controller\AppController;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['add', 'edit'])) {
            $this->set('categories', $this->PostsCategories->getTreeList());
        }
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can delete posts categories
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists posts categories
     * @return void
     * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function index()
    {
        $categories = $this->PostsCategories->find('all')
            ->select(['id', 'title', 'slug', 'post_count'])
            ->contain([
                'Parents' => function ($q) {
                    return $q->select(['title']);
                },
            ])
            ->order(['PostsCategories.lft' => 'ASC'])
            ->toArray();

        //Gets categories as tree list
        $treeList = $this->PostsCategories->getTreeList();

        //Changes the category titles, replacing them with the titles of the tree list
        $categories = array_map(function ($category) use ($treeList) {
            $category->title = $treeList[$category->id];
            
            return $category;
        }, $categories);

        $this->set(compact('categories'));
    }

    /**
     * Adds posts category
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $category = $this->PostsCategories->newEntity();

        if ($this->request->is('post')) {
            $category = $this->PostsCategories->patchEntity($category, $this->request->data);

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('category'));
    }

    /**
     * Edits posts category
     * @param string $id Posts category ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $category = $this->PostsCategories->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->PostsCategories->patchEntity($category, $this->request->data);

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('category'));
    }
    /**
     * Deletes posts category
     * @param string $id Posts category ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $category = $this->PostsCategories->get($id);

        //Before deleting, it checks if the category has some posts
        if (!$category->post_count) {
            if ($this->PostsCategories->delete($category)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        } else {
            $this->Flash->alert(__d('me_cms', 'Before deleting this, you must delete or reassign all items that belong to this element'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
