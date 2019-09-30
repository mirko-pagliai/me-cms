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
 * @since       2.27.0
 */
namespace MeCms\Controller\Admin;

use Cake\Event\EventInterface;
use MeCms\Controller\AppController as BaseAppController;

/**
 * Admin Application controller class
 */
class AppController extends BaseAppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(EventInterface $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        $this->viewBuilder()->setClassName('MeCms.View/Admin');

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.records');

        $this->Auth->deny();
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('MeTools.Uploader');
    }

    /**
     * Internal method to set an upload error.
     *
     * It saves the error as view var that `JsonView` should serialize and sets
     *  the response status code to 500.
     * @param string $error Error message
     * @return void
     * @since 2.18.1
     */
    protected function setUploadError(string $error): void
    {
        $this->setResponse($this->getResponse()->withStatus(500));
        $this->set(compact('error'));
        $this->set('_serialize', ['error']);
    }
}
