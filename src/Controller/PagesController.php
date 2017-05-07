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

use Cake\Event\Event;
use MeCms\Controller\AppController;
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
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.Controller.Controller.html#_beforeFilter
     * @uses MeCms\Controller\AppController::beforeFilter()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

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
     * @return \Cake\Network\Response|void
     * @uses MeCms\Utility\StaticPage::get()
     * @uses MeCms\Utility\StaticPage::title()
     */
    public function view($slug = null)
    {
        //Checks if there exists a static page
        $static = StaticPage::get($slug);

        if ($static) {
            $page = (object)am([
                'category' => (object)['slug' => null, 'title' => null],
                'title' => StaticPage::title($slug),
                'subtitle' => null,
            ], compact('slug'));

            $this->set(compact('page'));

            return $this->render($static);
        }

        $page = $this->Pages->find('active')
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->where([sprintf('%s.slug', $this->Pages->getAlias()) => $slug])
            ->cache(sprintf('view_%s', md5($slug)), $this->Pages->cache)
            ->firstOrFail();

        $this->set(compact('page'));
    }

    /**
     * Preview for pages.
     * It uses the `view` template.
     * @param string $slug Page slug
     * @return void
     */
    public function preview($slug = null)
    {
        $page = $this->Pages->find('pending')
            ->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
            ->contain([$this->Pages->Categories->getAlias() => ['fields' => ['title', 'slug']]])
            ->where([sprintf('%s.slug', $this->Pages->getAlias()) => $slug])
            ->firstOrFail();

        $this->set(compact('page'));

        $this->render('view');
    }
}
