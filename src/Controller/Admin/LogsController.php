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

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use MeCms\Controller\AppController;

/**
 * Logs controller
 */
class LogsController extends AppController
{
    /**
     * Internal method to get the path for a log
     * @param string $filename Filename
     * @param bool $serialized `true` for a serialized log
     * @return string
     */
    protected function getPath($filename, $serialized)
    {
        if ($serialized) {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '_serialized.log';
        }

        return LOGS . $filename;
    }

    /**
     * Internal method to read a log content
     * @param string $filename Filename
     * @param bool $serialized `true` for a serialized log
     * @return string|array Log as array for serialized logs, otherwise a string
     * @uses getPath()
     */
    protected function read($filename, $serialized)
    {
        $log = $this->getPath($filename, $serialized);

        is_readable_or_fail($log);

        $log = file_get_contents($log);

        return $serialized ? safe_unserialize($log) : trim($log);
    }

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
     * Lists logs
     * @return void
     * @uses getPath()
     */
    public function index()
    {
        //Gets all log files, except those serialized
        $logs = collection((new Folder(LOGS))->find('(?!.*_serialized).+\.log'))
            ->map(function ($log) {
                return new Entity([
                    'filename' => $log,
                    'hasSerialized' => is_readable($this->getPath($log, true)),
                    'size' => filesize(LOGS . $log),
                ]);
            });

        $this->set(compact('logs'));
    }

    /**
     * Views a log
     * @param string $filename Filename
     * @return void
     * @uses read()
     */
    public function view($filename)
    {
        $serialized = false;

        if ($this->request->getQuery('as') === 'serialized') {
            $serialized = true;
            $this->viewBuilder()->setTemplate('view_as_serialized');
        }

        $content = $this->read($filename, $serialized);

        $this->set(compact('content', 'filename'));
    }

    /**
     * Downloads a log
     * @param string $filename Log filename
     * @return \Cake\Network\Response
     * @uses getPath()
     */
    public function download($filename)
    {
        $file = $this->getPath($filename, false);

        return $this->response->withFile($file, ['download' => true]);
    }

    /**
     * Deletes a log.
     * If there's even a serialized log copy, it also deletes that.
     * @param string $filename Filename
     * @return \Cake\Network\Response|null
     * @uses getPath()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        $success = (new File($this->getPath($filename, false)))->delete();
        $serialized = $this->getPath($filename, true);

        //Deletes the serialized log copy, if it exists
        if (file_exists($serialized)) {
            $successSerialized = (new File($serialized))->delete();
        }

        if ($success && $successSerialized) {
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        return $this->redirect(['action' => 'index']);
    }
}
