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

use Cake\I18n\Time;
use Cake\ORM\ResultSet;
use Cake\View\Cell;

/**
 * PostsWidgets cell
 */
class PostsWidgetsCell extends Cell
{
    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        $this->loadModel('MeCms.Posts');
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

        $months = $this->Posts->find('active')
            ->select('created')
            ->formatResults(function (ResultSet $results) {
                return $results->sortBy('created', SORT_DESC)
                    ->countBy(function ($post) {
                        return $post->get('created')->i18nFormat('yyyy/MM');
                    })
                    ->map(function ($countBy, $month) {
                        return [
                            'created' => Time::createFromFormat('Y/m/d H:i:s', $month . '/01 00:00:00'),
                            'post_count' => $countBy,
                        ];
                    });
            })
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
