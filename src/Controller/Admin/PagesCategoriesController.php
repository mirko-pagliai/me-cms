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
use MeCms\Controller\Admin\AppController;
use MeCms\Model\Entity\PagesCategory;

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
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function beforeFilter(Event $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        if ($this->getRequest()->isAction(['add', 'edit'])) {
            $this->set('categories', $this->PagesCategories->getTreeList());
        }
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can delete pages categories. Admins and managers can access other actions
        return $this->Auth->isGroup($this->getRequest()->isDelete() ? ['admin'] : ['admin', 'manager']);
    }

    /**
     * Lists pages categories
     * @return void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function index()
    {
        $categories = $this->PagesCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->orderAsc(sprintf('%s.lft', $this->PagesCategories->getAlias()))
            ->formatResults(function (ResultSet $results) {
                //Gets categories as tree list
                $treeList = $this->PagesCategories->getTreeList()->toArray();

                return $results->map(function (PagesCategory $category) use ($treeList) {
                    return $category->set('title', $treeList[$category->get('id')]);
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

        if ($this->getRequest()->is('post')) {
            $category = $this->PagesCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Add pages category'));
        $this->render('form');
    }

    /**
     * Edits pages category
     * @param string $id Pages category ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id)
    {
        $category = $this->PagesCategories->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $category = $this->PagesCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect($this->referer(['action' => 'index']));
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Edit pages category'));
        $this->render('form');
    }

    /**
     * Deletes pages category
     * @param string $id Pages category ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $category = $this->PagesCategories->get($id);

        list($method, $message) = ['alert', I18N_BEFORE_DELETE];
        //Before deleting, it checks if the category has some pages
        if (!$category->get('page_count')) {
            $this->PagesCategories->deleteOrFail($category);
            list($method, $message) = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect($this->referer(['action' => 'index']));
    }
}
