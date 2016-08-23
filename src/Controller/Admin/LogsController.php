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

/**
 * Logs controller
 */
class LogsController extends AppController
{
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
     * @param bool $serialized true if is a serialized log
     * @return string
     */
    protected function _path($filename, $serialized = false)
    {
        if ($serialized) {
            $filename = sprintf(
                '%s_serialized.%s',
                pathinfo($filename, PATHINFO_FILENAME),
                pathinfo($filename, PATHINFO_EXTENSION)
            );
        }

        return LOGS . $filename;
    }

    /**
     * Lists logs
     * @return void
     */
    public function index()
    {
        //Gets all log files
        $logs = (new Folder(LOGS))->read(true, ['empty'])[1];

        $logs = af(array_map(function ($log) {
            //Return, if this is a serialized log
            if (preg_match('/_serialized\.log$/i', $log)) {
                return;
            }

            //If this log has a serialized copy
            $serialized = is_readable(LOGS . sprintf('%s_serialized.log', pathinfo($log, PATHINFO_FILENAME)));

            return (object)am([
                'filename' => $log,
                'size' => filesize(LOGS . $log),
            ], compact('serialized'));
        }, $logs));

        $this->set(compact('logs'));
    }

    /**
     * Views a log
     * @param string $filename Filename
     * @return void
     * @throws InternalErrorException
     * @uses _path()
     */
    public function view($filename)
    {
        $log = $this->_path($filename);

        if (!is_readable($log)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
        }

        $log = (object)am([
            'content' => trim(file_get_contents($log)),
        ], compact('filename'));

        $this->set(compact('log'));
    }

    /**
     * Views a (serialized) log
     * @param string $filename Filename
     * @return void
     * @throws InternalErrorException
     * @uses _path()
     */
    public function viewSerialized($filename)
    {
        $log = $this->_path($filename, true);

        if (!is_readable($log)) {
            throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
        }

        $log = (object)am([
            'content' => unserialize(file_get_contents($log)),
        ], compact('filename'));

        $this->set(compact('log'));
    }

    /**
     * Downloads a log
     * @param string $filename Filename
     * @return \Cake\Network\Response
     * @uses MeCms\Controller\AppController::_download()
     * @uses _path()
     */
    public function download($filename)
    {
        $log = $this->_path($filename);

        return $this->_download($log);
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
