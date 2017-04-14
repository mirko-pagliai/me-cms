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
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PagesCategoriesTable::getList()
     * @uses MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     * @uses MeCms\Model\Table\UsersTable::getActiveList()
     * @uses MeCms\Model\Table\UsersTable::getList()
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isIndex()) {
            $categories = $this->Pages->Categories->getList();
        } elseif ($this->request->isAction(['add', 'edit'])) {
            $categories = $this->Pages->Categories->getTreeList();
        }

        //Checks for categories
        if (isset($categories) && empty($categories) && !$this->request->isIndex()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PagesCategories', 'action' => 'index']);
        }

        if (!empty($categories)) {
            $this->set(compact('categories'));
        }
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction(['add', 'edit'])) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty the
     *  user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Everyone can list pages and static pages
        if ($this->request->isAction(['index', 'indexStatics'])) {
            return true;
        }

        //Only admins can delete pages
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists pages
     * @return void
     * @uses MeCms\Model\Table\PagesTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Pages->find()->contain(['Categories' => ['fields' => ['id', 'title']]]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $pages = $this->paginate($this->Pages->queryFromFilter($query, $this->request->getQuery()));

        $this->set(compact('pages'));
    }

    /**
     * List static pages.
     *
     * Static pages must be located in `APP/View/StaticPages/`.
     * @return void
     * @uses MeCms\Utility\StaticPage::all()
     */
    public function indexStatics()
    {
        $this->set('pages', StaticPage::all());
    }

    /**
     * Adds page
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $page = $this->Pages->newEntity();

        if ($this->request->is('post')) {
            $page = $this->Pages->patchEntity($page, $this->request->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('page'));
    }

    /**
     * Edits page
     * @param string $id Page ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $page = $this->Pages->findById($id)
            ->formatResults(function ($results) {
                return $results->map(function ($row) {
                    $row->created = $row->created->i18nFormat(FORMAT_FOR_MYSQL);

                    return $row;
                });
            })
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $page = $this->Pages->patchEntity($page, $this->request->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('page'));
    }
    /**
     * Deletes page
     * @param string $id Page ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $page = $this->Pages->get($id);

        if ($this->Pages->delete($page)) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
