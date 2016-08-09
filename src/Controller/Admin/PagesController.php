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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use Cake\I18n\Time;
use MeCms\Controller\AppController;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before 
     *  each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\PagesCategoriesTable::getList()
	 * @uses MeCms\Model\Table\PagesCategoriesTable::getTreeList()
	 * @uses MeCms\Model\Table\UsersTable::getActiveList()
	 * @uses MeCms\Model\Table\UsersTable::getList()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		if($this->request->is('action', 'index')) {
			$categories = $this->Pages->Categories->getList();
		}
		elseif($this->request->is('action', ['add', 'edit'])) {
			$categories = $this->Pages->Categories->getTreeList();
		}
		
		//Checks for categories
		if(isset($categories) && empty($categories) && !$this->request->is('action', 'index')) {
			$this->Flash->alert(__d('me_cms', 'You must first create a category'));
			return $this->redirect(['controller' => 'PagesCategories', 'action' => 'index']);
		}
		
		if(!empty($categories)) {
			$this->set(compact('categories'));
        }
	}
    
	/**
	 * Initialization hook method
	 * @uses MeCms\Controller\AppController::initialize()
	 */
	public function initialize() {
        parent::initialize();
        
        //Loads KcFinderComponent
        if($this->request->is('action', ['add', 'edit'])) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }
	
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the 
     *  user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {
		//Everyone can list pages and static pages
		if($this->request->is('action', ['index', 'index_statics'])) {
			return TRUE;
        }
        
		//Only admins can delete pages
		if($this->request->is('action', 'delete')) {
			return $this->Auth->isGroup('admin');
        }
        
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
     * Lists pages
	 * @uses MeCms\Model\Table\PagesTable::queryFromFilter()
     */
    public function index() {
		$query = $this->Pages->find()
            ->select(['id', 'title', 'slug', 'priority', 'active', 'created'])
			->contain([
                'Categories' => function($q) {
                    return $q->select(['id', 'title']);
                }
            ]);
		
		$this->paginate['order'] = ['Pages.created' => 'DESC'];
		$this->paginate['sortWhitelist'] = ['title', 'Categories.title', 'priority', 'Pages.created'];
		
        $pages = $this->paginate($this->Pages->queryFromFilter($query, $this->request->query));
        
		$this->set(compact('pages'));
    }
		
	/**
	 * List static pages.
	 * 
	 * Static pages must be located in `APP/View/StaticPages/`.
	 * @uses MeCms\Utility\StaticPage::all()
	 */
	public function index_statics() {
		$this->set('pages', StaticPage::all());
	}

    /**
     * Adds page
     */
    public function add() {
        $page = $this->Pages->newEntity();
		
        if($this->request->is('post')) {
			$this->request->data['created'] = new Time($this->request->data('created'));
			
            $page = $this->Pages->patchEntity($page, $this->request->data);
			
            if($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
                return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('page'));
    }

    /**
     * Edits page
     * @param string $id Page ID
     */
    public function edit($id = NULL)  {
        $page = $this->Pages->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
			$this->request->data['created'] = new Time($this->request->data('created'));
			
            $page = $this->Pages->patchEntity($page, $this->request->data);
			
            if($this->Pages->save($page)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
                return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('page'));
    }
    /**
     * Deletes page
     * @param string $id Page ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $page = $this->Pages->get($id);
		
        if($this->Pages->delete($page)) {
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        }
        else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}