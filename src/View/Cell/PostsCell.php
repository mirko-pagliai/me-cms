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
namespace MeCms\View\Cell;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\View\Cell;

/**
 * Posts cell
 */
class PostsCell extends Cell {
	/**
	 * Constructor. It loads the model
	 * @param \MeTools\Network\Request $request The request to use in the cell
	 * @param \Cake\Network\Response $response The request to use in the cell
	 * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
	 * @param array $cellOptions Cell options to apply
	 * @uses Cake\View\Cell::__construct()
	 */
	public function __construct(\MeTools\Network\Request $request = NULL, \Cake\Network\Response $response = NULL, \Cake\Event\EventManager $eventManager = NULL, array $cellOptions = []) {
		parent::__construct($request, $response, $eventManager, $cellOptions);
		
		$this->loadModel('MeCms.Posts');
	}
	
	/**
	 * Categories widget
	 * @uses MeTools\Network\Request::isHere()
	 */
	public function categories() {
		//Returns on categories index
		if($this->request->isHere(['_name' => 'posts_categories'])) {
			return;
        }
		
		//Tries to get data from the cache
		$categories = Cache::read($cache = 'widget_categories', $this->Posts->cache);
		
		//If the data are not available from the cache
        if(empty($categories)) {
			$categories = $this->Posts->Categories->find('active')
				->select(['title', 'slug', 'post_count'])
				->order(['title' => 'ASC'])
				->toArray();
			
			foreach($categories as $k => $category) {
				$categories[$category->slug] = sprintf('%s (%d)', $category->title, $category->post_count);
				unset($categories[$k]);
			}
			
            Cache::write($cache, $categories, $this->Posts->cache);
		}
		
		$this->set(compact('categories'));
	}
	
	/**
	 * Latest widget
	 * @param int $limit Limit
	 * @uses MeTools\Network\Request::isAction()
	 */
    public function latest($limit = 10) {
		//Returns on index, except for category
		if($this->request->isAction('index', 'Posts') && !$this->request->param('slug')) {
			return;
        }

		$posts = $this->Posts->find('active')
			->select(['title', 'slug'])
			->limit($limit)
			->order(['created' => 'DESC'])
			->cache(sprintf('widget_latest_%d', $limit), $this->Posts->cache)
			->toArray();
        
        $this->set(compact('posts'));
    }
    
    /**
     * Posts by month widget
     */
    public function months() {
		//Returns on index
		if($this->request->isAction('index', 'Posts')) {
			return;
        }
        
		//Tries to get data from the cache
		$months = Cache::read($cache = 'widget_months', $this->Posts->cache);
        
		//If the data are not available from the cache
        if(empty($months)) {
            $months = $this->Posts->find('active')
                ->select([
                    'month' => 'DATE_FORMAT(created, "%m-%Y")',
                    'post_count' => 'COUNT(DATE_FORMAT(created, "%m-%Y"))',
                ])
                ->distinct(['month'])
                ->order(['created' => 'DESC'])
                ->toArray();
            
            foreach($months as $k => $month) {
                $exploded = explode('-', $month->month);
                $months[$month->month] = sprintf('%s (%s)', (new Time())->year($exploded[1])->month($exploded[0])->day(1)->i18nFormat('MMMM y'), $month->post_count);
                unset($months[$k]);
            }
            
            Cache::write($cache, $months, $this->Posts->cache);
        }
        
        $this->set(compact('months'));
    }
	
	/**
	 * Search widget
	 */
	public function search() {
		//For this widget, control of the action takes place in the view
	}
    
    /**
     * Posts by year widget
     */
    public function years() {
		//Returns on index
		if($this->request->isAction('index', 'Posts')) {
			return;
        }
        
		//Tries to get data from the cache
		$years = Cache::read($cache = 'widget_years', $this->Posts->cache);

        //If the data are not available from the cache
        if(empty($years)) {
            $years = $this->Posts->find('active')
                ->select([
                    'year' => 'DATE_FORMAT(created, "%Y")',
                    'post_count' => 'COUNT(DATE_FORMAT(created, "%Y"))',
                ])
                ->distinct(['year'])
                ->order(['created' => 'DESC'])
                ->toArray();
            
            foreach($years as $k => $year) {
                $years[$year->year] = sprintf('%s (%s)', $year->year, $year->post_count);
                unset($years[$k]);
            }
            
            Cache::write($cache, $years, $this->Posts->cache);
        }
        
        $this->set(compact('years'));
    }
}