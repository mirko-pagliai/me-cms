<?php
declare(strict_types=1);

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

use MeCms\ORM\Query;

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
    public function index(): void
    {
        $categories = $this->PagesCategories->find('active')
            ->select(['title', 'slug'])
            ->orderAsc($this->PagesCategories->getAlias() . '.title')
            ->cache('categories_index')
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Lists pages for a category
     * @param string $slug Category slug
     * @return \Cake\Http\Response|null|void
     */
    public function view(string $slug)
    {
        //The category can be passed as query string, from a widget
        if ($this->getRequest()->getQuery('q')) {
            return $this->redirect([$this->getRequest()->getQuery('q')]);
        }

        $category = $this->PagesCategories->findActiveBySlug($slug)
            ->select(['id', 'title'])
            ->contain($this->PagesCategories->Pages->getAlias(), fn(Query $query): Query => $query->find('active')->select(['category_id', 'slug', 'title']))
            ->cache('category_' . md5($slug))
            ->firstOrFail();

        $this->set(compact('category'));
    }
}
