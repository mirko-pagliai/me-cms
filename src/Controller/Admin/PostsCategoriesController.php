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
use MeCms\Model\Entity\PostsCategory;

/**
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController
{
    /**
     * Called after the controller action is run, but before the view is rendered
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     * @uses \MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        if ($this->getRequest()->is('action', ['add', 'edit'])) {
            $this->set('categories', $this->PostsCategories->getTreeList());
        }
    }

    /**
     * Lists posts categories
     * @return void
     */
    public function index(): void
    {
        $treeList = $this->PostsCategories->getTreeList()->toArray();
        $categories = $this->PostsCategories->find()
            ->contain(['Parents' => ['fields' => ['title']]])
            ->orderAsc($this->PostsCategories->getAlias() . '.lft')
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->map(fn(PostsCategory $category): PostsCategory => $category->set('title', $treeList[$category->get('id')])));

        $this->set(compact('categories'));
    }

    /**
     * Adds posts category
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $category = $this->PostsCategories->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $category = $this->PostsCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Add posts category'));
        $this->render('form');
    }

    /**
     * Edits posts category
     * @param string $id Posts category ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $category = $this->PostsCategories->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $category = $this->PostsCategories->patchEntity($category, $this->getRequest()->getData());

            if ($this->PostsCategories->save($category)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('category'));
        $this->set('title', __d('me_cms', 'Edit posts category'));
        $this->render('form');
    }

    /**
     * Deletes posts category
     * @param string $id Posts category ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        //Before deleting, it checks if the category has some posts
        $category = $this->PostsCategories->get($id);
        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        if (!$category->get('post_count')) {
            $this->PostsCategories->deleteOrFail($category);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        }
        $this->Flash->$method($message);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
