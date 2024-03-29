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

namespace MeCms\View\Cell;

use Cake\Collection\CollectionInterface;
use Cake\ORM\ResultSet;
use Cake\View\Cell;
use MeCms\Model\Table\PagesTable;

/**
 * PagesWidgets cell
 */
class PagesWidgetsCell extends Cell
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected PagesTable $Pages;

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        /** @var \MeCms\Model\Table\PagesTable $Pages */
        $Pages = $this->getTableLocator()->get('MeCms.Pages');
        $this->Pages = $Pages;
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories(string $render = 'form'): void
    {
        $this->viewBuilder()->setTemplate('categories_as_' . $render);

        //Returns on categories index
        if ($this->request->is('url', ['_name' => 'pagesCategories'])) {
            return;
        }

        $categories = $this->Pages->Categories->find('active')
            ->select(['title', 'slug', 'page_count'])
            ->orderAsc(sprintf('%s.title', $this->Pages->Categories->getAlias()))
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->indexBy('slug'))
            ->cache('widget_categories')
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Pages list widget
     * @return void
     */
    public function pages(): void
    {
        //Returns on pages index
        if ($this->request->is('url', ['_name' => 'pagesCategories'])) {
            return;
        }

        $pages = $this->Pages->find('active')
            ->select(['title', 'slug'])
            ->orderAsc('title')
            ->cache('widget_list')
            ->all();

        $this->set(compact('pages'));
    }
}
