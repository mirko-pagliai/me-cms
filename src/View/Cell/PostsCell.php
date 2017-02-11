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

use Cake\I18n\FrozenDate;
use Cake\View\Cell;

/**
 * Posts cell
 */
class PostsCell extends Cell
{
    /**
     * Constructor. It loads the model
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     * @uses Cake\View\Cell::__construct()
     */
    public function __construct(
        \Cake\Network\Request $request = null,
        \Cake\Network\Response $response = null,
        \Cake\Event\EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel('MeCms.Posts');
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories($render = 'form')
    {
        //Returns on categories index
        if ($this->request->isUrl(['_name' => 'postsCategories'])) {
            return;
        }

        $categories = $this->Posts->Categories->find('active')
            ->select(['title', 'slug', 'post_count'])
            ->order([sprintf('%s.title', $this->Posts->Categories->alias()) => 'ASC'])
            ->formatResults(function ($results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_categories', $this->Posts->cache)
            ->toArray();

        $this->set(compact('categories'));

        if ($render === 'list') {
            $this->viewBuilder()->template(sprintf('categories_as_%s', $render));
        }
    }

    /**
     * Latest widget
     * @param int $limit Limit
     * @return void
     */
    public function latest($limit = 10)
    {
        //Returns on posts index
        if ($this->request->isUrl(['_name' => 'posts'])) {
            return;
        }

        $posts = $this->Posts->find('active')
            ->select(['title', 'slug'])
            ->limit($limit)
            ->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
            ->cache(sprintf('widget_latest_%d', $limit), $this->Posts->cache)
            ->toArray();

        $this->set(compact('posts'));
    }

    /**
     * Posts by month widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function months($render = 'form')
    {
        //Returns on posts index
        if ($this->request->isUrl(['_name' => 'posts'])) {
            return;
        }

        $query = $this->Posts->find('active');
        $time = $query->func()->date_format(['created' => 'identifier', "'%Y/%m'" => 'literal']);
        $months = $query->select([
                'month' => $time,
                'post_count' => $query->func()->count($time),
            ])
            ->distinct(['month'])
            ->formatResults(function ($results) {
                return $results->indexBy('month')->map(function ($row) {
                    list($year, $month) = explode('/', $row->month);
                    $row->month = (new FrozenDate())->day(1)->month($month)->year($year);

                    return $row;
                });
            })
            ->order(['month' => 'DESC'])
            ->cache('widget_months', $this->Posts->cache)
            ->toArray();

        $this->set(compact('months'));

        if ($render === 'list') {
            $this->viewBuilder()->template(sprintf('months_as_%s', $render));
        }
    }

    /**
     * Search widget
     * @return void
     */
    public function search()
    {
        //For this widget, control of the action takes place in the view
    }
}
