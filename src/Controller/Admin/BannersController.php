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

use MeCms\Controller\AppController;
use MeCms\Controller\Traits\DownloadTrait;

/**
 * Banners controller
 * @property \MeCms\Model\Table\BannersTable $Banners
 */
class BannersController extends AppController
{
    use DownloadTrait;

    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *   each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\BannersPositions::getList()
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['index', 'edit', 'upload'])) {
            //Gets positions
            $positions = $this->Banners->Positions->getList();

            //Checks for positions
            if (empty($positions) && !$this->request->isIndex()) {
                $this->Flash->alert(__d('me_cms', 'You must first create a banner position'));

                return $this->redirect(['controller' => 'BannersPositions', 'action' => 'index']);
            }

            $this->set(compact('positions'));
        }
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

        if ($this->Cookie->read('render.banners') === 'grid' && !$render) {
            return $this->redirect([
                '?' => am($this->request->getQuery(), ['render' => 'grid'])
            ]);
        }

        $query = $this->Banners->find()->contain([
            'Positions' => function ($q) {
                return $q->select(['id', 'title']);
            },
        ]);

        $this->paginate['order'] = ['created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = config('admin.photos');
        }

        $this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->request->getQuery())));

        if ($render) {
            $this->Cookie->write('render.banners', $render);

            if ($render === 'grid') {
                $this->render('index_as_grid');
            }
        }
    }

    /**
     * Uploads banners
     * @return void
     * @uses MeTools\Controller\Component\UploaderComponent::error()
     * @uses MeTools\Controller\Component\UploaderComponent::mimetype()
     * @uses MeTools\Controller\Component\UploaderComponent::save()
     * @uses MeTools\Controller\Component\UploaderComponent::set()
     */
    public function upload()
    {
        $position = $this->request->getQuery('position');

        //If there's only one position, it automatically sets the query value
        if (!$position && count($this->viewVars['positions']) < 2) {
            $this->request = $this->request->withQueryParams(['position' => firstKey($this->viewVars['positions'])]);
        }

        if ($this->request->getData('file')) {
            if (empty($position)) {
                throw new InternalErrorException(__d('me_cms', 'Missing position ID'));
            }

            http_response_code(500);

            $uploaded = $this->Uploader->set($this->request->getData('file'))
                ->mimetype('image')
                ->save(BANNERS);

            if (!$uploaded) {
                exit($this->Uploader->error());
            }

            $saved = $this->Banners->save($this->Banners->newEntity([
                'position_id' => $position,
                'filename' => basename($uploaded),
            ]));

            if (!$saved) {
                exit(__d('me_cms', 'The banner could not be saved'));
            }

            http_response_code(200);

            $this->render(false);
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
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('banner'));
    }

    /**
     * Downloads banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response
     * @uses MeCms\Controller\Traits\DownloadTrait::_download()
     */
    public function download($id = null)
    {
        return $this->_download($this->Banners->get($id)->path);
    }

    /**
     * Deletes banner
     * @param string $id Banner ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $banner = $this->Banners->get($id);

        if ($this->Banners->delete($banner)) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
