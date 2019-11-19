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

use Cake\ORM\Entity;
use MeCms\Controller\Admin\AppController;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Logs controller
 */
class LogsController extends AppController
{
    /**
     * Internal method to get the path for a log
     * @param string|\SplFileInfo $file File as filename string or a
     *  `SplFileInfo` instance
     * @param bool $serialized `true` for a serialized log
     * @return string
     */
    protected function getPath($file, $serialized)
    {
        $filename = $file instanceof SplFileInfo ? $file->getFilename() : $file;
        $filename = $serialized ? pathinfo($filename, PATHINFO_FILENAME) . '_serialized.log' : $filename;

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

        return $serialized ? @unserialize($log) : trim($log);
    }

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
     * Lists logs
     * @return void
     * @uses getPath()
     */
    public function index()
    {
        $finder = new Finder();
        $finder->files()->name('/^(?!.*_serialized).+\.log$/')->in(LOGS);
        $logs = array_map(function (SplFileInfo $log) {
            return new Entity([
                'filename' => $log->getFilename(),
                'hasSerialized' => is_readable($this->getPath($log, true)),
                'size' => $log->getSize(),
            ]);
        }, iterator_to_array($finder));

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
        if ($this->getRequest()->getQuery('as') === 'serialized') {
            $serialized = true;
            $this->viewBuilder()->setTemplate('view_as_serialized');
        }

        $this->set('content', $this->read($filename, $serialized));
        $this->set(compact('filename'));
    }

    /**
     * Downloads a log
     * @param string $filename Log filename
     * @return \Cake\Network\Response
     * @uses getPath()
     */
    public function download($filename)
    {
        return $this->getResponse()->withFile($this->getPath($filename, false), ['download' => true]);
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
        $this->getRequest()->allowMethod(['post', 'delete']);

        $success = @unlink($this->getPath($filename, false));
        $serialized = $this->getPath($filename, true);

        //Deletes the serialized log copy, if it exists
        if (file_exists($serialized)) {
            $successSerialized = @unlink($serialized);
        }

        list($method, $message) = ['error', I18N_OPERATION_NOT_OK];
        if ($success && $successSerialized) {
            list($method, $message) = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect(['action' => 'index']);
    }
}
