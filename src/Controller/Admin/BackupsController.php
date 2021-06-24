<?php
declare(strict_types=1);

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
use Cake\Http\Response;
use Cake\ORM\Entity;
use DatabaseBackup\Utility\BackupImport;
use DatabaseBackup\Utility\BackupManager;
use MeCms\Controller\Admin\AppController;
use MeCms\Form\BackupForm;

/**
 * Backups controller
 * @see \DatabaseBackup\Utility\BackupManager
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
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null): bool
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->BackupManager = $this->BackupManager ?: new BackupManager();
        $this->BackupImport = $this->BackupImport ?: new BackupImport();
    }

    /**
     * Internal method to get a filename. It decodes the filename and adds the
     *  backup target directory
     * @param string $filename Filename
     * @return string
     * @since 2.18.3
     */
    protected function getFilename(string $filename): string
    {
        return getConfigOrFail('DatabaseBackup.target') . DS . urldecode($filename);
    }

    /**
     * Lists backup files
     * @return void
     * @uses $BackupManager
     */
    public function index(): void
    {
        $backups = $this->BackupManager->index()->map(function (Entity $backup): Entity {
            return $backup->set('slug', urlencode($backup->get('filename')));
        });

        $this->set(compact('backups'));
    }

    /**
     * Adds a backup file
     * @return \Cake\Http\Response|null|void
     * @see \MeCms\Form\BackupForm
     */
    public function add()
    {
        $backup = new BackupForm();

        if ($this->getRequest()->is('post')) {
            if ($backup->execute($this->getRequest()->getData())) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('backup'));
    }

    /**
     * Internal method to delete backup files
     * @param string|null $filename  Backup filename or `null` to delete all
     * @return \Cake\Http\Response|null
     * @uses getFilename()
     * @uses $BackupManager
     */
    protected function _delete(?string $filename = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $filename = $filename ? $this->getFilename($filename) : null;
        if ($filename) {
            $this->BackupManager->delete($filename);
        } else {
            $this->BackupManager->deleteAll();
        }
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }

    /**
     * Deletes a backup file
     * @param string $filename Backup filename
     * @return \Cake\Http\Response|null
     * @uses _delete()
     */
    public function delete(string $filename): ?Response
    {
        return $this->_delete($filename);
    }

    /**
     * Deletes all backup files
     * @return \Cake\Http\Response|null
     * @uses _delete()
     */
    public function deleteAll(): ?Response
    {
        return $this->_delete();
    }

    /**
     * Downloads a backup file
     * @param string $filename Backup filename
     * @return \Cake\Http\Response
     * @uses getFilename()
     */
    public function download(string $filename): Response
    {
        return $this->getResponse()->withFile($this->getFilename($filename), ['download' => true]);
    }

    /**
     * Restores a backup file
     * @param string $filename Backup filename
     * @return \Cake\Http\Response|null
     * @uses getFilename()
     * @uses $BackupImport
     */
    public function restore(string $filename): ?Response
    {
        //Imports and clears the cache
        $this->BackupImport->filename($this->getFilename($filename))->import();
        Cache::clearAll();

        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }

    /**
     * Sends a backup file via mail
     * @param string $filename Backup filename
     * @return \Cake\Http\Response|null
     * @since 2.18.3
     * @uses getFilename()
     * @uses $BackupManager
     */
    public function send(string $filename): ?Response
    {
        $this->BackupManager->send($this->getFilename($filename), getConfigOrFail('email.webmaster'));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
