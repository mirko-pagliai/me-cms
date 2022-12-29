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
use Cake\I18n\FrozenTime;
use Cake\ORM\ResultSet;
use Cake\View\Cell;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\PostsTable;

/**
 * PostsWidgets cell
 */
class PostsWidgetsCell extends Cell
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected PostsTable $Posts;

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        /** @var \MeCms\Model\Table\PostsTable $Posts */
        $Posts = $this->getTableLocator()->get('MeCms.Posts');
        $this->Posts = $Posts;
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
        if ($this->request->is('url', ['_name' => 'postsCategories'])) {
            return;
        }

        $categories = $this->Posts->Categories->find('active')
            ->select(['title', 'slug', 'post_count'])
            ->orderAsc(sprintf('%s.title', $this->Posts->Categories->getAlias()))
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->indexBy('slug'))
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
        if ($this->request->is('url', ['_name' => 'posts'])) {
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
        $this->viewBuilder()->setTemplate('months_as_' . $render);

        //Returns on posts index
        if ($this->request->is('url', ['_name' => 'posts'])) {
            return;
        }

        $months = $this->Posts->find('active')
            ->select('created')
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->sortBy('created')
                ->countBy(fn(Post $post): string => $post->get('created')->i18nFormat('yyyy/MM'))
                ->map(fn(int $countBy, string $month): array => [
                    'created' => FrozenTime::createFromFormat('Y/m/d H:i:s', $month . '/01 00:00:00'),
                    'post_count' => $countBy,
                ]))
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
