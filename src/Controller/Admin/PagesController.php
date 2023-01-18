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

namespace MeCms\Controller\Admin;

use Cake\Collection\CollectionInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\ResultSet;
use MeCms\Model\Entity\Page;
use MeCms\Model\Entity\User;
use MeCms\Utility\StaticPage;

/**
 * Pages controller
 * @property \MeCms\Model\Table\PagesTable $Pages
 */
class PagesController extends AppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getList()
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function beforeFilter(EventInterface $event)
    {
        $parent = parent::beforeFilter($event);
        if ($parent) {
            return $parent;
        }

        //Returns for `indexStatics` action
        if ($this->getRequest()->is('action', 'indexStatics')) {
            return;
        }

        $methodToCall = $this->getRequest()->is('action', ['add', 'edit']) ? 'getTreeList' : 'getList';
        $categories = $this->Pages->Categories->$methodToCall()->all();
        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PagesCategories', 'action' => 'index']);
        }

        $this->set(compact('categories'));
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        //Everyone can list pages and static pages
        if ($this->getRequest()->is('action', ['index', 'indexStatics'])) {
            return true;
        }

        return parent::isAuthorized($User);
    }

    /**
     * Lists pages
     * @return void
     */
    public function index(): void
    {
        $query = $this->Pages->find()->contain(['Categories' => ['fields' => ['id', 'title']]]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $pages = $this->paginate($this->Pages->queryFromFilter($query, $this->getRequest()->getQueryParams()));

        $this->set(compact('pages'));
    }

    /**
     * List static pages.
     *
     * Static pages must be located in `APP/View/StaticPages/`.
     * @return void
     * @throws \ErrorException
     */
    public function indexStatics(): void
    {
        $this->set('pages', StaticPage::all());
    }

    /**
     * Adds page
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $page = $this->Pages->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $page = $this->Pages->patchEntity($page, $this->getRequest()->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('page'));
        $this->set('title', __d('me_cms', 'Add page'));
        $this->render('form');
    }

    /**
     * Edits page
     * @param string $id Page ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $page = $this->Pages->findById($id)
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->map(fn(Page $page): Page => $page->set('created', $page->get('created')->i18nFormat(FORMAT_FOR_MYSQL))))
            ->firstOrFail();

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $page = $this->Pages->patchEntity($page, $this->getRequest()->getData());

            if ($this->Pages->save($page)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('page'));
        $this->set('title', __d('me_cms', 'Edit page'));
        $this->render('form');
    }

    /**
     * Deletes page
     * @param string $id Page ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $this->Pages->deleteOrFail($this->Pages->get($id));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
