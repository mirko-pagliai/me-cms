<?php
/**
 * PagesController
 *
 * This file is part of MeCms Backend
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
 * @package		MeCmsBackend\Controller
 */

App::uses('MeCmsBackendAppController', 'MeCmsBackend.Controller');
/**
 * Pages Controller
 */
class PagesController extends MeCmsBackendAppController {
	/**
	 * List pages
	 */
	public function admin_index() {
		$this->set(array(
			'pages'				=> $this->Page->getList(),
			'title_for_layout'	=> __d('me_cms_backend', 'Pages')
		));
	}
	
	/**
	 * View page
	 * @param string $id Page id
	 */
	public function admin_view($id = NULL) {
		$this->set(array(
			'page'				=> $this->Page->getPage($id),
			'title_for_layout'	=> __d('me_cms_backend', 'Page')
		));
	}
}