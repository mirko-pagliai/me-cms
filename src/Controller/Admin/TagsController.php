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
 * Tags controller
 * @property \MeCms\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can edit tags
		if($this->request->isAction('edit'))
			return $this->Auth->isGroup(['admin', 'manager']);
		
		return TRUE;
	}
	
	/**
     * Edits tag
     * @param string $id Tag ID
	 */
    public function edit($id = NULL)  {
        $tag = $this->Tags->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->data);
			
            if($this->Tags->save($tag)) {
                $this->Flash->success(__d('me_cms', 'The tag has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The tag could not be saved'));
        }

        $this->set(compact('tag'));
	}
}