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

use MeCms\Controller\AppController;

/**
 * BannersPositions controller
 * @property \MeCms\Model\Table\BannersPositionsTable $BannersPositions
 */
class BannersPositionsController extends AppController {
	/**
	 * Checks if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isGroup('admin');
	}
	
	/**
     * Lists positions
     */
    public function index() {
		$this->paginate['order'] = ['name' => 'ASC'];
		
        $positions = $this->paginate(
			$this->BannersPositions->find()
                ->select(['id', 'name', 'description', 'banner_count'])
        );
        
		$this->set(compact('positions'));
    }

    /**
     * Adds banners position
     */
    public function add() {
        $position = $this->BannersPositions->newEntity();
		
        if($this->request->is('post')) {
            $position = $this->BannersPositions->patchEntity($position, $this->request->data);
			
            if($this->BannersPositions->save($position)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
				return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('position'));
    }

    /**
     * Edits banners position
     * @param string $id Banners Position ID
     */
    public function edit($id = NULL)  {
        $position = $this->BannersPositions->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $position = $this->BannersPositions->patchEntity($position, $this->request->data);
			
            if($this->BannersPositions->save($position)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
                return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('position'));
    }
    /**
     * Deletes banners position
     * @param string $id Banners Position ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $position = $this->BannersPositions->get($id);
		
		//Before deleting, it checks if the position has some banners
		if(!$position->banner_count) {
			if($this->BannersPositions->delete($position)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
            }
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
		}
		else {
			$this->Flash->alert(__d('me_cms', 'Before deleting this, you must delete or reassign all items that belong to this element'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}