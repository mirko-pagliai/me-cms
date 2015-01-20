<?php
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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');
App::uses('StaticPage', 'MeCms.Utility');

/**
 * Pages Controller
 */
class PagesController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAdmin()
	 * @uses MeAuthComponenet::isManager()
	 * @uses MeToolsAppController::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and manager can add and edit pages
		if($this->isAction(array('admin_add', 'admin_edit')))
			return $this->Auth->isManager();
		
		//Only admins can delete pages
		if($this->isAction('admin_delete'))
			return $this->Auth->isAdmin();
		
		return TRUE;
	}

	/**
	 * Add page
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->Page->create();
			if($this->Page->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The page has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The page could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms', 'Add page'));
	}

	/**
	 * Delete page
	 * @param string $id Page id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Page->id = $id;
		if(!$this->Page->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Page->delete())
			$this->Session->flash(__d('me_cms', 'The page has been deleted'));
		else
			$this->Session->flash(__d('me_cms', 'The page was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}

	/**
	 * Edit page
	 * @param string $id Page id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Page->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Page->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The page has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The page could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Page->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'title', 'slug', 'text', 'priority', 'active', 'created')
			));

		$this->set('title_for_layout', __d('me_cms', 'Edit page'));
	}
	
	/**
	 * List pages
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'title', 'slug', 'priority', 'active', 'created'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'pages'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Pages')
		));
	}
	
	/**
	 * List static pages.
	 * 
	 * Static pages must be located in `APP/View/StaticPages/`.
	 * @uses StaticPage::getAll()
	 */
	public function admin_index_statics() {
		$this->set(array(
			'pages'				=> StaticPage::getAll(),
			'title_for_layout'	=> __d('me_cms', 'Static pages')
		));
	}
	
	/**
	 * List pages
	 */
	public function index() {
		//Tries to get data from the cache
		$pages = Cache::read($cache = 'pages_index', 'pages');
		
		//If the data are not available from the cache
        if(empty($pages)) {
            $pages = $this->Page->find('active', array('fields' => array('title', 'slug')));
			
            Cache::write($cache, $pages, 'pages');
        }
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Pages')), compact('pages')));
	}
	
	/**
	 * View page.
	 * 
	 * It first checks if there exists a static page, using all the passed arguments.
	 * Otherwise, it looks for the page in the database, using the slug.
	 * 
	 * Static pages must be located in `APP/View/StaticPages/`.
	 * @param string $slug Page slug
	 * @throws NotFoundException
	 * @uses StaticPage::exists()
	 */
	public function view($slug = NULL) {
		//Checks if there exists a static page, using all the passed arguments
		if(StaticPage::exists($args = func_get_args())) {
			//Sets the relative path
			$path = implode(DS, $args);
			
			//Sets the title (the last argument)			
			$this->set('title_for_layout', Inflector::humanize(str_replace('-', '_', $args[count($args)-1])));
			
			return $this->render('StaticPages'.DS.$path);
		}
		
		//Tries to get data from the cache
		$page = Cache::read($cache = sprintf('pages_view_%s', $slug), 'pages');
		
		//If the data are not available from the cache
        if(empty($page)) {
			$page = $this->Page->find('active', array(
				'conditions'	=> array('slug' => $slug),
				'fields'		=> array('title', 'subtitle', 'slug', 'text', 'created'),
				'limit'			=> 1
			));
			
			if(empty($page))
				throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
            Cache::write($cache, $page, 'pages');
        }
		
		$this->set(am(array('title_for_layout' => $page['Page']['title']), compact('page')));
	}
	
	/**
	 * Gets the pages list for widget.
	 * This method works only with `requestAction()`.
	 * @return array Pages list
	 * @throws ForbiddenException
	 * @uses MeToolsAppController::isRequestAction()
	 */
	public function widget_list() {
		//This method works only with "requestAction()"
		if(!$this->isRequestAction())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$pages = Cache::read($cache = 'pages_widget_list', 'pages');
		
		//If the data are not available from the cache
        if(empty($pages)) {
            $pages = $this->Page->find('active', array('fields' => array('title', 'slug')));
			
            Cache::write($cache, $pages, 'pages');
        }
		
		return $pages;
	}
}