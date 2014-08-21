<?php
App::uses('MeCmsAppController', 'MeCms.Controller');

/**
 * PagesController
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

/**
 * Pages Controller
 */
class PagesController extends MeCmsAppController {
	/**
	 * List pages
	 */
	public function admin_index() {
		$this->set(array(
			'pages'				=> $this->Page->getList(),
			'title_for_layout'	=> __d('me_cms', 'Pages')
		));
	}
	
	/**
	 * View page
	 * @param string $id Page id
	 */
	public function admin_view($id = NULL) {
		$this->set(array(
			'page'				=> $this->Page->getPage($id),
			'title_for_layout'	=> __d('me_cms', 'Page')
		));
	}
	
	/**
	 * View page
	 * @throws MissingViewException
	 * @throws NotFoundException
	 */
	public function view() {
		$path = func_get_args();
		
		try {
			$this->render(implode('/', $path));
		} 
		catch(MissingViewException $e) {
			if(Configure::read('debug'))
				throw $e;
			throw new NotFoundException();
		}
		
		$this->set('title_for_layout', Inflector::humanize($path[count($path) - 1]));
	}
}