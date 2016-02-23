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

use DatabaseBackup\Utility\BackupManager;
use MeCms\Controller\AppController;

/**
 * Backups controller
 */
class BackupsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isGroup('admin');
	}
	
	/**
	 * Lists backup files
	 * @uses DatabaseBackup\Utility\BackupManager::index()
	 */
	public function index() {
		$this->set('backups', BackupManager::index());
	}
	
	/**
	 * Deletes a backup file
	 * @param string $filename Backup filename
	 * @uses DatabaseBackup\Utility\BackupManager::delete()
	 */
	public function delete($filename) {
        $this->request->allowMethod(['post', 'delete']);
		
		if(BackupManager::delete(urldecode($filename)))
			$this->Flash->success(__d('me_cms', 'The backup has been deleted'));
		else
			$this->Flash->error(__d('me_cms', 'The backup could not be deleted'));
		
        return $this->redirect(['action' => 'index']);
	}

	/**
	 * Downloads a backup file
	 * @param string $filename Backup filename
	 * @uses DatabaseBackup\Utility\BackupManager::path()
	 */
	public function download($filename) {
		$this->response->file(BackupManager::path(urldecode($filename)));
		return $this->response;
	}
}