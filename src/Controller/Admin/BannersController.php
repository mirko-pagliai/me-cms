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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\InternalErrorException;
use MeCms\Controller\Admin\AppController;

/**
 * Banners controller
 * @property \MeCms\Model\Table\BannersTable $Banners
 */
class BannersController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *   each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\BannersPositions::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        //Gets positions
        $positions = $this->Banners->Positions->getList();

        if ($positions->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a banner position'));

            return $this->redirect(['controller' => 'BannersPositions', 'action' => 'index']);
        }

        $this->set(compact('positions'));
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *   the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can delete banners. Admins and managers can access other actions
        return $this->Auth->isGroup($this->getRequest()->isDelete() ? ['admin'] : ['admin', 'manager']);
    }

    /**
     * Lists banners.
     *
     * This action can use the `index_as_grid` template.
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Model\Table\BannersTable::queryFromFilter()
     */
    public function index()
    {
        //The "render" type can set by query or by cookies
        $render = $this->getRequest()->getQuery('render', $this->getRequest()->getCookie('render-banners'));

        $query = $this->Banners->find()->contain(['Positions' => ['fields' => ['id', 'title']]]);

        $this->paginate['order'] = ['created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.photos');
            $this->viewBuilder()->setTemplate('index_as_grid');
        }

        $this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->getRequest()->getQueryParams())));
        $this->set('title', I18N_BANNERS);

        if ($render) {
            $this->response = $this->response->withCookie((new Cookie('render-banners', $render))->withNeverExpire());
        }
    }

    /**
     * Uploads banners
     * @return null
     * @throws \Cake\Http\Exception\InternalErrorException
     * @uses \MeCms\Controller\AppController::setUploadError()
     * @uses \MeTools\Controller\Component\UploaderComponent
     */
    public function upload()
    {
        $position = $this->getRequest()->getQuery('position');
        $positions = $this->viewVars['positions']->toArray();

        //If there's only one available position
        if (!$position && count($positions) < 2) {
            $position = array_key_first($positions);
            $this->setRequest($this->getRequest()->withQueryParams(compact('position')));
        }

        if ($this->getRequest()->getData('file')) {
            is_true_or_fail($position, __d('me_cms', 'Missing ID'), InternalErrorException::class);

            $uploaded = $this->Uploader->set($this->getRequest()->getData('file'))
                ->mimetype('image')
                ->save(BANNERS);

            if (!$uploaded) {
                return $this->setUploadError($this->Uploader->getError());
            }

            $entity = $this->Banners->newEntity([
                'position_id' => $position,
                'filename' => basename($uploaded),
            ]);

            if ($entity->getErrors()) {
                return $this->setUploadError(array_value_first_recursive($entity->getErrors()));
            }

            if (!$this->Banners->save($entity)) {
                $this->setUploadError(I18N_OPERATION_NOT_OK);
            }
        }
    }

    /**
     * Edits banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id)
    {
        $banner = $this->Banners->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $banner = $this->Banners->patchEntity($banner, $this->getRequest()->getData());

            if ($this->Banners->save($banner)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index', $banner->get('position_id')]);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('banner'));
    }

    /**
     * Downloads banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response
     */
    public function download($id)
    {
        return $this->response->withFile($this->Banners->get($id)->get('path'), ['download' => true]);
    }

    /**
     * Deletes banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id)
    {
        $banner = $this->Banners->get($id);

        $this->getRequest()->allowMethod(['post', 'delete']);
        $this->Banners->deleteOrFail($banner);
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index', $banner->get('position_id')]);
    }
}
