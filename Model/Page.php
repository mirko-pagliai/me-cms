<?php
App::uses('Folder', 'Utility');
App::uses('MeCmsBackendAppModel', 'MeCmsBackend.Model');

/**
 * Page
 *
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Model
 */

/**
 * Page Model
 */
class Page extends MeCmsBackendAppModel {
	/**
	 * Pages path.
	 * It will be set in the constructor.
	 * @var string 
	 */
	private $path;
	
	/**
	 * This model doesn't use a database table
	 * @var boolean 
	 */
    public $useTable = FALSE;
	
	/**
	 * Construct function
	 */
	public function __construct() {
		//Sets the pages path
		$this->path = APP.'View'.DS.'Pages'.DS;
		
		parent::__construct();
	}
	
	/**
	 * Gets the path for a page
	 * @param int $id Page id
	 * @return string Page path
	 * @uses getList() to get the list of pages
	 */
	private function _getPath($id) {		
		$path = FALSE;
		
		foreach($this->getList() as $page)
			if($page['Page']['id'] == $id) {
				$path = $this->path.$page['Page']['filename'];
				break;
			}
		
		return $path;
	}

	/**
	 * Gets the list of pages
	 * @return array List of pages
	 */
	public function getList() {
		//Checks if is readable
		if(!is_readable($this->path))
			return array();
		
		$dir = new Folder($this->path);
		$files = $dir->findRecursive('.+\.ctp', TRUE);
				
		return array_map(function($filename, $id) {
			$filename = preg_replace(sprintf('/^%s/', preg_quote($this->path, '/')), '', $filename);
			$args = pathinfo($filename, PATHINFO_DIRNAME);
			
			if($args != '.')
				$args .= DS.pathinfo($filename, PATHINFO_FILENAME);
			else
				$args = pathinfo($filename, PATHINFO_FILENAME);
			
			return array('Page' => compact('id', 'filename', 'args'));
		}, $files, range(1, count($files)));
	}
	
	/**
	 * Gets the content for a page
	 * @param int $id Page id
	 * @return string Page content
	 * @uses _getPath() to get the page path
	 */
	public function getPage($id) {		
		if(!is_readable($path = $this->_getPath($id)))
			return FALSE;
		
		//Gets the content of the page
		$content = file_get_contents($path);

		return array('Page' => compact('id', 'content', 'path'));
	}
}