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

use Cake\I18n\Time;
use Cake\ORM\ResultSet;
use Cake\View\Cell;
use MeCms\Model\Entity\Post;

/**
 * PostsWidgets cell
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsWidgetsCell extends Cell
{
    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        $this->loadModel('MeCms.Posts');
    }

    /**
     * Categories widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function categories(string $render = 'form'): void
    {
        $this->viewBuilder()->setTemplate(sprintf('categories_as_%s', $render));

        //Returns on categories index
        if ($this->request->isUrl(['_name' => 'postsCategories'])) {
            return;
        }

        $categories = $this->Posts->Categories->find('active')
            ->select(['title', 'slug', 'post_count'])
            ->orderAsc(sprintf('%s.title', $this->Posts->Categories->getAlias()))
            ->formatResults(function (ResultSet $results) {
                return $results->indexBy('slug');
            })
            ->cache('widget_categories')
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Latest widget
     * @param int $limit Limit
     * @return void
     */
    public function latest(int $limit = 10): void
    {
        //Returns on posts index
        if ($this->request->isUrl(['_name' => 'posts'])) {
            return;
        }

        $posts = $this->Posts->find('active')
            ->select(['title', 'slug'])
            ->limit($limit)
            ->orderDesc('created')
            ->cache('widget_latest_' . $limit)
            ->all();

        $this->set(compact('posts'));
    }

    /**
     * Posts by month widget
     * @param string $render Render type (`form` or `list`)
     * @return void
     */
    public function months(string $render = 'form'): void
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
                    ->countBy(function (Post $post): string {
                        return $post->get('created')->i18nFormat('yyyy/MM');
                    })
                    ->map(function (int $countBy, string $month): array {
                        return [
                            'created' => Time::createFromFormat('Y/m/d H:i:s', $month . '/01 00:00:00'),
                            'post_count' => $countBy,
                        ];
                    });
            })
            ->cache('widget_months')
            ->all();

        $this->set(compact('months'));
    }

    /**
     * Search widget
     * @return void
     */
    public function search(): void
    {
        //For this widget, control of the action takes place in the view
    }
}
