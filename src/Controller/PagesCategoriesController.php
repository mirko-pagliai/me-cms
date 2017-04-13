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
namespace MeCms\Controller;

use MeCms\Controller\AppController;

/**
 * PagesCategories controller
 * @property \MeCms\Model\Table\PagesCategoriesTable $PagesCategories
 */
class PagesCategoriesController extends AppController
{
    /**
     * Lists pages categories
     * @return void
     */
    public function index()
    {
        $categories = $this->PagesCategories->find('active')
            ->select(['title', 'slug'])
            ->order([sprintf('%s.title', $this->PagesCategories->getAlias()) => 'ASC'])
            ->cache('categories_index', $this->PagesCategories->cache)
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Lists pages for a category
     * @param string $slug Category slug
     * @return \Cake\Network\Response|null|void
     */
    public function view($slug = null)
    {
        //The category can be passed as query string, from a widget
        if ($this->request->getQuery('q')) {
            return $this->redirect([$this->request->getQuery('q')]);
        }

        $category = $this->PagesCategories->find('active')
            ->select(['id', 'title'])
            ->contain(['Pages' => function ($q) {
                return $q->select(['category_id', 'slug', 'title']);
            }])
            ->where([sprintf('%s.slug', $this->PagesCategories->getAlias()) => $slug])
            ->cache(sprintf('category_%s', md5($slug)), $this->PagesCategories->cache)
            ->firstOrFail();

        $this->set(compact('category'));
    }
}
