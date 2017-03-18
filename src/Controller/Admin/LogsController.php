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

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;
use MeCms\Controller\Traits\DownloadTrait;

/**
 * Logs controller
 */
class LogsController extends AppController
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
     * Lists logs
     * @return void
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
        $log = (object)am([
            'content' => $this->_read($filename),
        ], compact('filename'));

        $this->set(compact('log'));
    }

    /**
     * Views a (serialized) log
     * @param string $filename Filename
     * @return void
     * @uses _read()
     */
    public function viewSerialized($filename)
    {
        $log = (object)am([
            'content' => $this->_read($filename, true),
        ], compact('filename'));

        $this->set(compact('log'));
    }

    /**
     * Downloads a log
     * @param string $filename Filename
     * @return \Cake\Network\Response
     * @uses MeCms\Controller\Traits\DownloadTrait::_download()
     * @uses _path()
     */
    public function download($filename)
    {
        return $this->_download($this->_path($filename));
    }

    /**
     * Deletes a log.
     * If there's even a serialized log copy, it also deletes that.
     * @param string $filename Filename
     * @return \Cake\Network\Response|null
     * @throws InternalErrorException
     * @uses _path()
     */
    public function delete($filename)
    {
        $this->request->allowMethod(['post', 'delete']);

        $log = $this->_path($filename);

        if (!is_writeable($log)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr($log)));
        }

        $success = (new File($log))->delete();

        $serialized = $this->_path($filename, true);

        //It also deletes the serialized log copy, where such exists
        if (file_exists($serialized)) {
            if (!is_writeable($serialized)) {
                throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr($serialized)));
            }

            if (!(new File($serialized))->delete()) {
                $success = false;
            }
        }

        if ($success) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
