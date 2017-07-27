<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
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
            ->cache('categories_index', $this->PagesCategories->cache);

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
            ->where([sprintf('%s.slug', $this->PagesCategories->getAlias()) => $slug])
            ->cache(sprintf('category_%s', md5($slug)), $this->PagesCategories->cache)
            ->firstOrFail();

        $pages = $this->PagesCategories->Pages->find('active')
            ->select(['slug', 'title'])
            ->where(['category_id' => $category->id])
            ->cache(sprintf('category_%s_pages', md5($slug)), $this->PagesCategories->cache)
            ->all();

        $this->set(compact('category', 'pages'));
    }
}
