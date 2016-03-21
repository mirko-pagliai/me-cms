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

use DatabaseBackup\Utility\Backup;
use DatabaseBackup\Utility\BackupImport;
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
	 * @uses DatabaseBackup\Utility\Backup::index()
	 */
	public function index() {
		$this->set('backups', Backup::index());
	}
	
	/**
	 * Adds a backup file
	 * @see MeCms\Form\BackupForm
	 * @see MeCms\Form\BackupForm::execute()
	 */
	public function add() {
		$backup = new \MeCms\Form\BackupForm();
		
		if($this->request->is('post')) {
			//Creates the backup
			if($backup->execute($this->request->data)) {
				$this->Flash->success(__d('me_cms', 'The backup has been created'));
				$this->redirect(['action' => 'index']);
			}
			else
				$this->Flash->error(__d('me_cms', 'The backup has not been created'));
		}
		
		$this->set(compact('backup'));
	}
	
	/**
	 * Deletes a backup file
	 * @param string $filename Backup filename
	 * @uses DatabaseBackup\Utility\Backup::delete()
	 */
	public function delete($filename) {
        $this->request->allowMethod(['post', 'delete']);
		
		if(Backup::delete(urldecode($filename)))
			$this->Flash->success(__d('me_cms', 'The backup has been deleted'));
		else
			$this->Flash->error(__d('me_cms', 'The backup could not be deleted'));
		
        return $this->redirect(['action' => 'index']);
	}

	/**
	 * Downloads a backup file
	 * @param string $filename Backup filename
	 * @uses DatabaseBackup\Utility\Backup::path()
	 */
	public function download($filename) {
		$this->response->file(Backup::path(urldecode($filename)));
		return $this->response;
	}
    
    /**
     * Restores a backup file
	 * @param string $filename Backup filename
	 * @uses DatabaseBackup\Utility\Backup::path()
	 * @uses DatabaseBackup\Utility\BackupImport::filename()
	 * @uses DatabaseBackup\Utility\BackupImport::import()
     * 
     */
    public function restore($filename) {
        $filename = Backup::path(urldecode($filename));
        
		$backup = new BackupImport();
		$backup->filename($filename);
        
        if($backup->import())
			$this->Flash->success(__d('me_cms', 'The backup has been restored'));
		else
			$this->Flash->error(__d('me_cms', 'The backup could not be restored'));
        
        return $this->redirect(['action' => 'index']);
    }
}