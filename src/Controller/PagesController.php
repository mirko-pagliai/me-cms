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

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Entity;
use Cake\Routing\Router;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        $this->Auth->deny('preview');
    }

    /**
     * Views page.
     *
     * It first checks if there's a static page, using all the passed
     *  arguments.
     * Otherwise, it checks for the page in the database, using that slug.
     *
     * Static pages must be located in `APP/View/StaticPages/`.
     * @param string $slug Page slug
     * @return \Cake\Http\Response|null|void
     */
    public function view(string $slug)
    {
        //Checks if there exists a static page
        $static = StaticPage::get($slug);

        if ($static) {
            $page = new Entity([
                'title' => StaticPage::getTitle($slug),
                'url' => Router::url(['_name' => 'page', $slug], true),
            ] + compact('slug'));

            $this->set(compact('page'));

            return $this->render($static);
        }

        $slug = rtrim($slug, '/');
        $page = $this->Pages->findActiveBySlug($slug)
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->cache('view_' . md5($slug))
            ->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Preview for pages.
     * It uses the `view` template.
     * @param string $slug Page slug
     * @return \Cake\Http\Response
     */
    public function preview(string $slug): Response
    {
        $page = $this->Pages->findPendingBySlug($slug)
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->firstOrFail();

        $this->set(compact('page'));

        return $this->render('view');
    }
}
