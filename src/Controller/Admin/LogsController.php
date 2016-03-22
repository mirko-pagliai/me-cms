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

use Cake\Filesystem\File;
use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;

/**
 * Logs controller
 */
class LogsController extends AppController {
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
     * Returns the path for a log 
	 * @param string $slug
     * @param bool $serialized TRUE if is a serialized log
     * @return string
     */
    protected function _logPath($slug, $serialized = FALSE) {
		return LOGS.sprintf($serialized ? '%s_serialized.log' : '%s.log', urldecode($slug));
    }
	
	/**
	 * Lists logs
	 */
	public function index() {
		//Gets all log files
		$logs = (new \Cake\Filesystem\Folder(LOGS))->read(TRUE, ['empty'])[1];		
				
		$logs = af(array_map(function($log){
			//Return, if this is a serialized log
			if(preg_match('/_serialized\.log$/i', $log))
				return;
			
			//If this log has a serialized copy
			$serialized = is_readable(LOGS.sprintf('%s_serialized.log', pathinfo($log, PATHINFO_FILENAME)));
			
			return (object) am([
				'filename' => $log,
				'size' => filesize(LOGS.$log),
				'slug' => urlencode(pathinfo($log, PATHINFO_FILENAME)),
			], compact('serialized'));
		}, $logs));
		
		$this->set(compact('logs'));
	}
	
	/**
	 * Views a log
	 * @param string $slug
	 * @throws InternalErrorException
     * @uses _logPath()
	 */
	public function view($slug) {
        $log = $this->_logPath($slug);
        
		if(!is_readable($log))
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
				
        $this->set('log', (object) [
            'content' => trim(file_get_contents($log)),
            'filename' => basename($log),
        ]);
	}
	
	/**
	 * Views a (serialized) log
	 * @param string $slug
	 * @throws InternalErrorException
     * @uses _logPath()
	 */
	public function view_serialized($slug) {		
        $log = $this->_logPath($slug, TRUE);
        
		if(!is_readable($log))
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
				
        $this->set('log', (object) [
			'content' => unserialize(file_get_contents($log)),
            'filename' => basename($log),
        ]);
	}
    
    /**
     * Downloads a log
	 * @param string $slug
     * @throws InternalErrorException
     * @uses _logPath()
     */
    public function download($slug) {
        $log = $this->_logPath($slug);
		
		if(!is_readable($log))
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not readable', rtr($log)));
                
		$this->response->file($log);
		return $this->response;
    }
    
    /**
     * Deletes a log.  
     * If there's even a serialized log copy, it also deletes that.
	 * @param string $slug
     * @throws InternalErrorException
     */
    public function delete($slug) {
        $this->request->allowMethod(['post', 'delete']);
        
        $log = $this->_logPath($slug);
		
		if(!is_writeable($log))
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr($log)));
        
        $success = (new File($log))->delete();
                
        $serialized = $this->_logPath($slug, TRUE);
        
        //It also deletes the serialized log copy, where such exists 
        if(file_exists($serialized)) {
            if(!is_writeable($serialized))
                throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr($serialized)));
            
            if(!(new File($serialized))->delete())
                $success = FALSE;
        }
        
        if($success)
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
		else
			$this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        
		return $this->redirect(['action' => 'index']);
    }
}