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
namespace MeCms\Controller;

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
			->cache('index', 'pages')
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
     * @throws \Cake\Network\Exception\NotFoundException
	 * @uses MeCms\Utility\StaticPage::exists()
	 * @uses MeCms\Utility\StaticPage::title()
	 */
    public function view($slug = NULL) {
		//Checks if there exists a static page, using all the passed arguments
		if(StaticPage::exists($args = func_get_args())) {
			$page = new \stdClass();
			$page->slug = $slug;
			$page->title = StaticPage::title($args);
			
			$this->set(compact('page'));
			
			return $this->render('StaticPages'.DS.implode(DS, $args));			
		}
		
		$this->set('page', $this->Pages->find('active')
			->select(['title', 'subtitle', 'slug', 'text', 'created'])
			->where(compact('slug'))
			->cache(sprintf('view_%s', md5($slug)), 'pages')
			->first());
    }
}