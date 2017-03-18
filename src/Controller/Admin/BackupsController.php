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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\DownloadTrait;
use MysqlBackup\Utility\BackupImport;
use MysqlBackup\Utility\BackupManager;

/**
 * Backups controller
 */
class BackupsController extends AppController
{
    use DownloadTrait;

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Lists backup files
     * @return void
     * @uses MysqlBackup\Utility\BackupManager::index()
     */
    public function index()
    {
        $backups = array_map(function ($backup) {
            $backup->slug = urlencode($backup->filename);

            return $backup;
        }, BackupManager::index());

        $this->set(compact('backups'));
    }

    /**
     * Adds a backup file
     * @return \Cake\Network\Response|null|void
     * @see MeCms\Form\BackupForm
     * @see MeCms\Form\BackupForm::execute()
     */
    public function add()
    {
        $backup = new \MeCms\Form\BackupForm();

        if ($this->request->is('post')) {
            //Creates the backup
            if ($backup->execute($this->request->data)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('backup'));
    }

    /**
     * Deletes a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses MysqlBackup\Utility\BackupManager::delete()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        if (BackupManager::delete(urldecode($filename))) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Deletes all backup files
     * @return \Cake\Network\Response|null
     * @uses MysqlBackup\Utility\BackupManager::deleteAll()
     */
    public function deleteAll()
    {
        $this->request->allowMethod(['post', 'delete']);

        if (BackupManager::deleteAll()) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Downloads a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response
     * @uses MeCms\Controller\Traits\DownloadTrait::_download()
     */
    public function download($filename)
    {
        return $this->_download(Configure::read('MysqlBackup.target') . DS . urldecode($filename));
    }

    /**
     * Restores a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses MysqlBackup\Utility\BackupImport::filename()
     * @uses MysqlBackup\Utility\BackupImport::import()
     */
    public function restore($filename)
    {
        $filename = Configure::read('MysqlBackup.target') . DS . urldecode($filename);

        $backup = new BackupImport();
        $backup->filename($filename);

        if ($backup->import()) {
            //Clears the cache
            Cache::clearAll();

            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
