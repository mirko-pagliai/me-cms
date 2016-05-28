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
namespace MeCms\Controller;

use MeCms\Controller\AppController;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.2/class-Cake.Controller.Controller.html#_beforeFilter
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
        parent::beforeFilter($event);
        
        $this->Auth->deny('view');
    }
    
	/**
     * Lists pages
     */
    public function index() {
		$this->set('pages', $this->Pages->find('active')
			->select(['title', 'slug'])
			->cache('index', $this->Pages->cache)
			->all());
    }
	
	/**
     * Views page.
	 * 
	 * It first checks if there's a static page, using all the passed arguments.
	 * Otherwise, it checks for the page in the database, using that slug.
	 * 
	 * Static pages must be located in `APP/View/StaticPages/`.
	 * @param string $slug Page slug
	 * @uses MeCms\Utility\StaticPage::get()
	 * @uses MeCms\Utility\StaticPage::title()
	 */
    public function view($slug = NULL) {
		//Checks if there exists a static page
		$static = StaticPage::get($slug);
		
		if($static) {
			$page = new \stdClass();
			$page->slug = $slug;
			$page->title = StaticPage::title($slug);
			
			$this->set(compact('page'));
			
			return $this->render($static);
		}
		
		$page = $this->Pages->find('active')
			->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
			->where(compact('slug'))
			->cache(sprintf('view_%s', md5($slug)), $this->Pages->cache)
			->firstOrFail();
        
        $this->set(compact('page'));
    }
    
    /**
     * Preview for pages.
     * It uses the `view` template.
	 * @param string $slug Page slug
     */
    public function preview($slug = NULL) {
		$page = $this->Pages->find()
			->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
			->where(compact('slug'))
			->firstOrFail();
        
        $this->set(compact('page'));
        
        $this->render('view');
    }
}