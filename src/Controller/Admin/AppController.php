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
use Cake\Http\Response;
use MeCms\Controller\AppController as BaseAppController;

/**
 * Admin Application controller class
 */
abstract class AppController extends BaseAppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An EventInterface instance
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(EventInterface $event): ?Response
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
     * Called after the controller action is run, but before the view is rendered
     * @param \Cake\Event\EventInterface $event An EventInterface instance
     * @return void
     * @since 2.27.5
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        //For `index` action, saves request target and current controller name
        //  in session as a referer. The controller name will be used to verify
        $request = $this->getRequest();
        if ($request->isAction('index')) {
            $request->getSession()->write('referer', [
                'controller' => $request->getParam('controller'),
                'target' => $request->getRequestTarget(),
            ]);
        }
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
     * Returns the referring URL for this request.
     *
     * Unlike the original method, this can return the `index` action of the same
     *  controller (if it has been indicated as the `$default` parameter),
     *  preserving also the query string
     * @param string|array|null $default Default URL to use if `HTTP_REFERER`
     *  cannot be read from headers
     * @param bool $local If `true`, restrict referring URLs to local server
     * @return string Referring URL
     * @see beforeRender()
     * @since 2.27.5
     */
    public function referer($default = '/', bool $local = false): string
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        if ($default == ['action' => 'index'] &&
            $session->read('referer.controller') == $request->getParam('controller')) {
            return $session->read('referer.target');
        }

        return parent::referer($default, $local);
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
