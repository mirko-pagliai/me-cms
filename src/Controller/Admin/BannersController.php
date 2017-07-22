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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;

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
        //Only admins can delete banners
        if ($this->request->isDelete()) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists banners.
     *
     * This action can use the `index_as_grid` template.
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Model\Table\BannersTable::queryFromFilter()
     */
    public function index()
    {
        $render = $this->request->getQuery('render');

        //The "render" type can also be set via cookies, if it's not set by query
        if (!$render && $this->Cookie->check('renderBanners')) {
            $render = $this->Cookie->read('renderBanners');
        }

        $query = $this->Banners->find()->contain(['Positions' => ['fields' => ['id', 'title']]]);

        $this->paginate['order'] = ['created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.photos');
        }

        $this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->request->getQuery())));

        if ($render) {
            $this->Cookie->write('renderBanners', $render);

            if ($render === 'grid') {
                $this->render('index_as_grid');
            }
        }
    }

    /**
     * Uploads banners
     * @return void
     * @throws InternalErrorException
     * @uses MeCms\Controller\AppController::setUploadError()
     * @uses MeTools\Controller\Component\UploaderComponent
     */
    public function upload()
    {
        $position = $this->request->getQuery('position');
        $positions = $this->viewVars['positions']->toArray();

        //If there's only one available position
        if (!$position && count($positions) < 2) {
            $position = collection(array_keys($positions))->first();
            $this->request = $this->request->withQueryParams(compact('position'));
        }

        if ($this->request->getData('file')) {
            if (!$position) {
                throw new InternalErrorException(__d('me_cms', 'Missing position ID'));
            }

            $uploaded = $this->Uploader->set($this->request->getData('file'))
                ->mimetype('image')
                ->save(BANNERS);

            if (!$uploaded) {
                $this->setUploadError($this->Uploader->error());

                return;
            }

            $entity = $this->Banners->newEntity([
                'position_id' => $position,
                'filename' => basename($uploaded),
            ]);

            if ($entity->getErrors()) {
                $this->setUploadError(collection(collection($entity->getErrors())->first())->first());

                return;
            }

            $saved = $this->Banners->save($entity);

            if (!$saved) {
                $this->setUploadError(__d('me_cms', 'The banner could not be saved'));
            }
        }
    }

    /**
     * Edits banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id = null)
    {
        $banner = $this->Banners->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $banner = $this->Banners->patchEntity($banner, $this->request->getData());

            if ($this->Banners->save($banner)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        $this->set(compact('banner'));
    }

    /**
     * Downloads banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response
     */
    public function download($id = null)
    {
        $file = $this->Banners->get($id)->path;

        return $this->response->withFile($file, ['download' => true]);
    }

    /**
     * Deletes banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->Banners->deleteOrFail($this->Banners->get($id));

        $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

        return $this->redirect(['action' => 'index']);
    }
}
