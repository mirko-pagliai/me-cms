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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * A component to handle KCFinder
 */
class KcFinderComponent extends Component {
	/**
	 * Components
	 * @var array
	 */
    public $components = ['MeCms.Auth'];
	
	/**
	 * Checks for KCFinder
	 * @return bool
	 * @uses path()
	 */
	public function check() {
		return is_readable($this->path().DS.'index.php');
	}
	
	/**
	 * Checks if the files directory is writeable
	 * @return bool
	 * @uses filesPath()
	 */
	public function checkFiles() {
		return is_writable($this->filesPath());
	}
	
	/**
	 * Sets the configuration for KCFinder
	 * @param array $options Options
	 * @return bool
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function configure(array $options = []) {
		if($this->request->session()->check('KCFINDER'))
			return TRUE;
		
		$default = [
			'denyExtensionRename'	=> TRUE,
			'denyUpdateCheck'		=> TRUE,
			'dirnameChangeChars'	=> [' ' => '_', ':' => '_'],
			'disabled'				=> FALSE,
			'filenameChangeChars'	=> [' ' => '_', ':' => '_'],
			'jpegQuality'			=> 100,
			'uploadDir'				=> WWW_ROOT.'files',
			'uploadURL'				=> Router::url('/files', TRUE),
			'types'					=> Configure::read('MeCms.kcfinder.types')
		];
		
		//If the user is not and admin
		if(!$this->Auth->isGroup(['admin'])) {
			//Only admins can delete or rename directories
			$default['access']['dirs'] = ['create' => TRUE, 'delete' => FALSE, 'rename' => FALSE];
			//Only admins can delete, move or rename files
			$default['access']['files'] = [
				'upload'	=> TRUE,
				'delete'	=> FALSE,
				'copy'		=> TRUE,
				'move'		=> FALSE,
				'rename'	=> FALSE
			];
		}

		return $this->request->session()->write('KCFINDER', am($default, $options));
	}
	
    /**
     * Alias for `getFilesPath()` method.
     * @see getFilesPath()
     */
    public function filesPath() {
        return call_user_func_array([get_class(), 'getFilesPath'], func_get_args());
    }
	
	/**
	 * Gets the files path
	 * @return string Path
	 */
	public function getFilesPath() {
		return WWW_ROOT.'files';
	}
	
	/**
	 * Gets the KCFinder path
	 * @return string Path
	 */
	public function getPath() {
		return WWW_ROOT.'kcfinder';
	}
	
    /**
     * Alias for `getPath()` method.
     * @see getPath()
     */
    public function path() {
        return call_user_func_array([get_class(), 'getPath'], func_get_args());
    }
}