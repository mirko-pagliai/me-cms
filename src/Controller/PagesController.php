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

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\Time;
use MeCms\Controller\AppController;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController {
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
		
		if(!empty($static)) {
			$page = new \stdClass();
			$page->slug = $slug;
			$page->title = StaticPage::title($slug);
			
			$this->set(compact('page'));
			
			return $this->render($static);
		}
		
		$page = $this->Pages->find()
			->select(['title', 'subtitle', 'slug', 'text', 'active', 'created'])
			->where(compact('slug'))
			->cache(sprintf('view_%s', md5($slug)), $this->Pages->cache)
			->firstOrFail();
		
        //Checks created datetime and status. Logged users can view future pages and drafts
        if(!$this->Auth->user() && ($page->created > new Time() || $page->active))
            throw new RecordNotFoundException(__d('me_cms', 'Record not found'));
        
        $this->set(compact('page'));
    }
}