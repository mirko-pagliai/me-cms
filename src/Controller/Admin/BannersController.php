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
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Query;
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

        $query = $this->Banners->find()->contain('Positions', function (Query $q) {
            return $q->select(['id', 'title']);
        });

        $this->paginate['order'] = ['created' => 'DESC'];

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if ($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.photos');
        }

        $this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->request->getQueryParams())));

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
                throw new InternalErrorException(__d('me_cms', 'Missing ID'));
            }

            $uploaded = $this->Uploader->set($this->request->getData('file'))
                ->mimetype('image')
                ->save(BANNERS);

            if (!$uploaded) {
                $this->setUploadError($this->Uploader->getError());

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
                $this->setUploadError(I18N_OPERATION_NOT_OK);
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
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
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

        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }
}
