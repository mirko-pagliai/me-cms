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
 * PagesCategories controller
 * @property \MeCms\Model\Table\PagesCategoriesTable $PagesCategories
 */
class PagesCategoriesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['add', 'edit'])) {
            $this->set('categories', $this->PagesCategories->getTreeList());
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
        //Only admins can delete pages categories
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists pages categories
     * @return void
     * @uses MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function index()
    {
        $categories = $this->PagesCategories->find('all')
            ->contain(['Parents' => function ($q) {
                return $q->select(['title']);
            }])
            ->order(['PagesCategories.lft' => 'ASC'])
            ->toArray();

        //Gets categories as tree list
        $treeList = $this->PagesCategories->getTreeList();

        //Changes the category titles, replacing them with the titles of the
        //  tree list
        $categories = array_map(function ($category) use ($treeList) {
            $category->title = $treeList[$category->id];

            return $category;
        }, $categories);

        $this->set(compact('categories'));
    }

    /**
     * Adds pages category
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $category = $this->PagesCategories->newEntity();

        if ($this->request->is('post')) {
            $category = $this->PagesCategories->patchEntity($category, $this->request->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('category'));
    }

    /**
     * Edits pages category
     * @param string $id Pages category ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $category = $this->PagesCategories->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->PagesCategories->patchEntity($category, $this->request->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('category'));
    }
    /**
     * Deletes pages category
     * @param string $id Pages category ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $category = $this->PagesCategories->get($id);

        //Before deleting, it checks if the category has some pages
        if (!$category->page_count) {
            if ($this->PagesCategories->delete($category)) {
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
