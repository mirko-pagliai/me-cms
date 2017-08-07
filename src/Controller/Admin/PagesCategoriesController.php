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
    public function beforeFilter(Event $event)
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
        $categories = $this->PagesCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->order([sprintf('%s.lft', $this->PagesCategories->alias()) => 'ASC'])
            ->formatResults(function ($categories) {
                //Gets categories as tree list
                $treeList = $this->PagesCategories->getTreeList()->toArray();

                return $categories->map(function ($category) use ($treeList) {
                    $category->title = $treeList[$category->id];

                    return $category;
                });
            });

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
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
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
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
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
            $this->PagesCategories->deleteOrFail($category);

            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
