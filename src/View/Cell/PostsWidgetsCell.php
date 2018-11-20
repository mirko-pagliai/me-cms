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
namespace MeCms\View\Cell;

use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\ResultSet;
use Cake\View\Cell;
use MeCms\Model\Entity\Post;

/**
 * PostsWidgets cell
 */
class PostsWidgetsCell extends Cell
{
    /**
     * Constructor. It loads the model
     * @param \Cake\Network\Request $request The request to use in the cell
     * @param \Cake\Network\Response $response The request to use in the cell
     * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
     * @param array $cellOptions Cell options to apply
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $cellOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $cellOptions);

        $this->loadModel(ME_CMS . '.Posts');
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories($render = 'form')
    {
        $this->viewBuilder()->setTemplate(sprintf('categories_as_%s', $render));

        //Returns on categories index
        if ($this->request->isUrl(['_name' => 'postsCategories'])) {
            return;
        }

        $categories = $this->Posts->Categories->find('active')
            ->select(['title', 'slug', 'post_count'])
            ->order([sprintf('%s.title', $this->Posts->Categories->getAlias()) => 'ASC'])
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_categories', $this->Posts->getCacheName())
            ->all();

        $this->set(compact('categories'));
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
            ->order([sprintf('%s.created', $this->Posts->getAlias()) => 'DESC'])
            ->cache(sprintf('widget_latest_%d', $limit), $this->Posts->getCacheName())
            ->all();

        $this->set(compact('posts'));
    }

    /**
     * Posts by month widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function months($render = 'form')
    {
        $this->viewBuilder()->setTemplate(sprintf('months_as_%s', $render));

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
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('month')
                    ->map(function (Post $post) {
                        list($year, $month) = explode('/', $post->month);
                        $post->month = (new FrozenDate())->day(1)->month($month)->year($year);

                        return $post;
                    });
            })
            ->order(['month' => 'DESC'])
            ->cache('widget_months', $this->Posts->getCacheName())
            ->all();

        $this->set(compact('months'));
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
