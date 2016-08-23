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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\Cell;

use Cake\View\Cell;

/**
 * Pages cell
 */
class PagesCell extends Cell
{
    /**
     * Constructor
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     * @return void
     * @uses Cake\View\Cell::__construct()
     */
    public function __construct(
        \Cake\Network\Request $request = null,
        \Cake\Network\Response $response = null,
        \Cake\Event\EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        //Loads the Photos model
        $this->loadModel('MeCms.Pages');
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories($render = 'form')
    {
        //Returns on categories index
        if ($this->request->isUrl(['_name' => 'pagesCategories'])) {
            return;
        }

        $categories = $this->Pages->Categories->find('active')
            ->select(['title', 'slug', 'page_count'])
            ->order(['title' => 'ASC'])
            ->cache('widget_categories', $this->Pages->cache)
            ->toArray();

        foreach ($categories as $k => $category) {
            $categories[$category->slug] = $category;
            unset($categories[$k]);
        }

        $this->set(compact('categories'));

        if ($render !== 'form') {
            $this->viewBuilder()->template(sprintf('categories_as_%s', $render));
        }
    }

    /**
     * Pages list widget
     * @return void
     */
    public function pages()
    {
        //Returns on pages index
        if ($this->request->isUrl(['_name' => 'pagesCategories'])) {
            return;
        }

        $pages = $this->Pages->find('active')
            ->select(['title', 'slug'])
            ->order(['title' => 'ASC'])
            ->cache(sprintf('widget_list'), $this->Pages->cache)
            ->toArray();

        $this->set(compact('pages'));
    }
}
