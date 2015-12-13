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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		if($this->request->isAction(['add', 'edit'])) {
			//Gets and sets categories
			$this->set('categories', $categories = $this->PostsCategories->getTreeList());
		}
	}
	
	/**
	 * Checks if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can delete posts categories
		if($this->request->isAction('delete'))
			return $this->Auth->isGroup('admin');
		
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
     * Lists posts categories
	 * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function index() {
		$categories = $this->PostsCategories->find('all')
			->contain(['Parents' => ['fields' => ['title']]])
			->order(['PostsCategories.lft' => 'ASC'])
			->select(['id', 'title', 'slug', 'post_count'])
			->toArray();
		
		//Changes the category titles, replacing them with the titles of the tree list
		array_walk($categories, function(&$category, $k, $treeList) {
			$category->title = $treeList[$category->id];
		}, $this->PostsCategories->getTreeList());
		
        $this->set(compact('categories'));
    }

    /**
     * Adds posts category
     */
    public function add() {
        $category = $this->PostsCategories->newEntity();
		
        if($this->request->is('post')) {
            $category = $this->PostsCategories->patchEntity($category, $this->request->data);
			
            if($this->PostsCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The posts category has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The posts category could not be saved'));
        }

        $this->set(compact('category'));
    }

    /**
     * Edits posts category
     * @param string $id Posts category ID
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function edit($id = NULL)  {
        $category = $this->PostsCategories->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->PostsCategories->patchEntity($category, $this->request->data);
			
            if($this->PostsCategories->save($category)) {
                $this->Flash->success(__d('me_cms', 'The posts category has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The posts category could not be saved'));
        }

        $this->set(compact('category'));
    }
    /**
     * Deletes posts category
     * @param string $id Posts category ID
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $category = $this->PostsCategories->get($id);
		
		//Before deleting, it checks if the category has some posts
		if(!$category->post_count) {
			if($this->PostsCategories->delete($category))
				$this->Flash->success(__d('me_cms', 'The posts category has been deleted'));
			else
				$this->Flash->error(__d('me_cms', 'The posts category could not be deleted'));
		}
		else
			$this->Flash->alert(__d('me_cms', 'Before you delete this category, you have to delete its posts or assign them to another category'));
		
        return $this->redirect(['action' => 'index']);
    }
}