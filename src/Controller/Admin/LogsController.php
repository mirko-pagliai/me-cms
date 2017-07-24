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
use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;

/**
 * Logs controller
 */
class LogsController extends AppController
{
    /**
     * Returns the path for a log
     * @param string $filename Filename
     * @param bool $serialized `true` for a serialized log
     * @return string
     */
    protected function _path($filename, $serialized = false)
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
     * @return string Log content
     * @throws InternalErrorException
     * @uses _path()
     */
    protected function _read($filename, $serialized = false)
    {
        $log = $this->_path($filename, $serialized);

        if (!is_readable($log)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
        }

        $log = file_get_contents($log);

        if ($serialized) {
            return unserialize($log);
        }

        return trim($log);
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
     * @uses _path()
     */
    public function index()
    {
        //Gets all log files, except those serialized
        $logs = (new Folder(LOGS))->find('(?!.*_serialized).+\.log');

        $logs = collection($logs)->map(function ($log) {
            return (object)[
                'filename' => $log,
                'hasSerialized' => is_readable($this->_path($log, true)),
                'size' => filesize(LOGS . $log),
            ];
        })->toList();

        $this->set(compact('logs'));
    }

    /**
     * Views a log
     * @param string $filename Filename
     * @return void
     * @uses _read()
     */
    public function view($filename)
    {
        $serialized = false;

        if ($this->request->getQuery('as') === 'serialized') {
            $serialized = true;
            $this->viewBuilder()->setTemplate('view_as_serialized');
        }

        $content = $this->_read($filename, $serialized);

        $this->set(compact('content', 'filename'));
    }

    /**
     * Downloads a log
     * @param string $filename Log filename
     * @return \Cake\Network\Response
     * @uses _path()
     */
    public function download($filename)
    {
        $file = $this->_path($filename);

        return $this->response->withFile($file, ['download' => true]);
    }

    /**
     * Deletes a log.
     * If there's even a serialized log copy, it also deletes that.
     * @param string $filename Filename
     * @return \Cake\Network\Response|null
     * @uses _path()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        $success = (new File($this->_path($filename)))->delete();

        $serialized = $this->_path($filename, true);

        //Deletes the serialized log copy, if it exists
        if (file_exists($serialized)) {
            $successSerialized = (new File($serialized))->delete();
        }

        if ($success && $successSerialized) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
