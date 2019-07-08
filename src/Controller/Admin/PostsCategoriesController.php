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
use Cake\ORM\ResultSet;
use MeCms\Controller\AppController;
use MeCms\Model\Entity\PostsCategory;

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
    public function beforeFilter(Event $event)
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
        //Only admins can delete posts categories. Admins and managers can access other actions
        return $this->Auth->isGroup($this->request->isDelete() ? ['admin'] : ['admin', 'manager']);
    }

    /**
     * Lists posts categories
     * @return void
     * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function index()
    {
        $categories = $this->PostsCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->order([sprintf('%s.lft', $this->PostsCategories->getAlias()) => 'ASC'])
            ->formatResults(function (ResultSet $results) {
                //Gets categories as tree list
                $treeList = $this->PostsCategories->getTreeList()->toArray();

                return $results->map(function (PostsCategory $category) use ($treeList) {
                    return $category->set('title', $treeList[$category->id]);
                });
            });

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
            $category = $this->PostsCategories->patchEntity($category, $this->request->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
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
            $category = $this->PostsCategories->patchEntity($category, $this->request->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
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
            $this->PostsCategories->deleteOrFail($category);
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
