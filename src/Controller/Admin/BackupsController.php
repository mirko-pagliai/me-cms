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
use Cake\ORM\Entity;
use DatabaseBackup\Utility\BackupImport;
use DatabaseBackup\Utility\BackupManager;
use MeCms\Controller\Admin\AppController;
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
     * @var \DatabaseBackup\Utility\BackupImport
     */
    public $BackupImport;

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Initialization hook method
     * @return void
     * @uses \MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        $this->BackupManager = new BackupManager();
        $this->BackupImport = new BackupImport();
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
        return getConfigOrFail('DatabaseBackup.target') . DS . urldecode($filename);
    }

    /**
     * Lists backup files
     * @return void
     * @uses \DatabaseBackup\Utility\BackupManager::index()
     */
    public function index()
    {
        $backups = $this->BackupManager->index()->map(function (Entity $backup) {
            return $backup->set('slug', urlencode($backup->get('filename')));
        });

        $this->set(compact('backups'));
    }

    /**
     * Adds a backup file
     * @return \Cake\Network\Response|null|void
     * @see \MeCms\Form\BackupForm
     */
    public function add()
    {
        $backup = new BackupForm();

        if ($this->getRequest()->is('post')) {
            //Creates the backup
            if ($backup->execute($this->getRequest()->getData())) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('backup'));
    }

    /**
     * Deletes a backup file
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @uses \DatabaseBackup\Utility\BackupManager::delete()
     * @uses getFilename()
     */
    public function delete($filename)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $this->BackupManager->delete($this->getFilename($filename));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Deletes all backup files
     * @return \Cake\Network\Response|null
     * @uses \DatabaseBackup\Utility\BackupManager::deleteAll()
     */
    public function deleteAll()
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $this->BackupManager->deleteAll();
        $this->Flash->success(I18N_OPERATION_OK);

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
     * @uses \DatabaseBackup\Utility\BackupImport::filename()
     * @uses \DatabaseBackup\Utility\BackupImport::import()
     * @uses getFilename()
     * @uses $BackupImport
     */
    public function restore($filename)
    {
        //Imports and clears the cache
        $this->BackupImport->filename($this->getFilename($filename))->import();
        Cache::clearAll();

        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sends a backup file via mail
     * @param string $filename Backup filename
     * @return \Cake\Network\Response|null
     * @since 2.18.3
     * @uses \DatabaseBackup\Utility\BackupManager::send()
     * @uses getFilename()
     */
    public function send($filename)
    {
        $this->BackupManager->send($this->getFilename($filename), getConfigOrFail('email.webmaster'));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }
}
