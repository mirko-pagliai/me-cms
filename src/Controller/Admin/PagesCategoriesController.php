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
use MeCms\Model\Entity\PagesCategory;

/**
 * PagesCategories controller
 * @property \MeCms\Model\Table\PagesCategoriesTable $PagesCategories
 */
class PagesCategoriesController extends AppController
{
    /**
     * Called after the controller action is run, but before the view is rendered
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        if ($this->getRequest()->is('action', ['add', 'edit'])) {
            $this->set('categories', $this->PagesCategories->getTreeList());
        }
    }

    /**
     * Lists pages categories
     * @return void
     * @uses \MeCms\Model\Table\PagesCategoriesTable::getTreeList()
     */
    public function index(): void
    {
        $treeList = $this->PagesCategories->getTreeList()->toArray();
        $categories = $this->PagesCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->orderAsc(sprintf('%s.lft', $this->PagesCategories->getAlias()))
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->map(fn(PagesCategory $category): PagesCategory => $category->set('title', $treeList[$category->get('id')])));

        $this->set(compact('categories'));
    }

    /**
     * Adds pages category
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $category = $this->PagesCategories->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $category = $this->PagesCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Add pages category'));
        $this->render('form');
    }

    /**
     * Edits pages category
     * @param string $id Pages category ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $category = $this->PagesCategories->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $category = $this->PagesCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PagesCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Edit pages category'));
        $this->render('form');
    }

    /**
     * Deletes pages category
     * @param string $id Pages category ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $category = $this->PagesCategories->get($id);

        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        //Before deleting, it checks if the category has some pages
        if (!$category->get('page_count')) {
            $this->PagesCategories->deleteOrFail($category);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        }
        $this->Flash->$method($message);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
