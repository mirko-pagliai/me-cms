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

use Cake\Cache\Cache;
use DatabaseBackup\Utility\BackupImport;
use DatabaseBackup\Utility\BackupManager;
use MeCms\Controller\AppController;
use MeCms\Form\BackupForm;

/**
 * Backups controller
 */
class BackupsController extends AppController
{
    /**
     * @var \DatabaseBackup\Utility\BackupManager
     */
    public $BackupManager;

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
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        $this->BackupManager = new BackupManager;
    }

    /**
     * Internal method to get a filename. It decodes the filename and adds the
     *  backup target directory
     * @param string $filename Filename
     * @return string
     * @since 2.18.3
     */
    protected function getFilename($filename)
    {
        return getConfigOrFail(DATABASE_BACKUP . '.target') . DS . urldecode($filename);
    }

    /**
     * Lists backup files
     * @return void
     * @uses DatabaseBackup\Utility\BackupManager::index()
     */
    public function index()
    {
        $backups = collection($this->BackupManager->index())
            ->map(function ($backup) {
                $backup->slug = urlencode($backup->filename);

                return $backup;
            });

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
        $backup = new BackupForm;

        if ($this->request->is('post')) {
            //Creates the backup
            if ($backup->execute($this->request->getData())) {
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
     * @uses DatabaseBackup\Utility\BackupManager::delete()
     * @uses getFilename()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->BackupManager->delete($this->getFilename($filename));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Deletes all backup files
     * @return \Cake\Network\Response|null
     * @uses DatabaseBackup\Utility\BackupManager::deleteAll()
     */
    public function deleteAll()
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->BackupManager->deleteAll();

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Downloads a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response
     * @uses getFilename()
     */
    public function download($filename)
    {
        return $this->response->withFile($this->getFilename($filename), ['download' => true]);
    }

    /**
     * Restores a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses DatabaseBackup\Utility\BackupImport::filename()
     * @uses DatabaseBackup\Utility\BackupImport::import()
     * @uses getFilename()
     */
    public function restore($filename)
    {
        (new BackupImport)->filename($this->getFilename($filename))->import();

        //Clears the cache
        Cache::clearAll();

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sends a backup file via mail
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @since 2.18.3
     * @uses DatabaseBackup\Utility\BackupManager::send()
     * @uses getFilename()
     */
    public function send($filename)
    {
        $this->BackupManager->send($this->getFilename($filename), getConfigOrFail('email.webmaster'));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }
}
