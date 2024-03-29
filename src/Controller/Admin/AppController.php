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
use Cake\Routing\Router;
use MeCms\Controller\AppController as BaseAppController;
use MeCms\Model\Entity\User;

/**
 * Admin Application controller class
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
abstract class AppController extends BaseAppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|void
     */
    public function beforeFilter(EventInterface $event)
    {
        $parent = parent::beforeFilter($event);
        if ($parent instanceof Response) {
            return $parent;
        }

        //Checks if the user is authorized. The `ControllerHookPolicy` will call the `isAuthorized()` method
        $this->Authorization->authorize($this);

        //Sets paginate limit and maximum paginate limit
        //See http://book.cakephp.org/4/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('admin.records');

        $this->viewBuilder()->setClassName('MeCms.View/Admin/App');
    }

    /**
     * Called after the controller action is run, but before the view is rendered
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        //Saves the referer in session, excluding post and put requests
        $request = $this->getRequest();
        if (!$request->is('post') && !$request->is('put')) {
            $referer = $request->referer();
            if ($referer && $request->getRequestTarget() !== $referer) {
                $request->getSession()->write('referer', $referer);
            }
        }
    }

    /**
     * Initialization hook method
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('MeTools.Uploader');
        $this->loadComponent('Authorization.Authorization');

        $this->Authentication->setConfig('requireIdentity', true);
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        //Only administrators can perform the "delete" action
        if ($this->getRequest()->is('action', 'delete')) {
            return $User->get('group')->get('name') === 'admin';
        }

        //By default, administrators and managers are authorized
        return in_array($User->get('group')->get('name'), ['admin', 'manager']);
    }

    /**
     * Redirects to given $url, after turning off $this->autoRender.
     *
     * Unlike the common `redirect()` method, checks whether a referer has been saved in the session that coincides with
     *  the requested redirect (and which could contain a query string, for example).
     * @param string|array|\Psr\Http\Message\UriInterface $url A string, array-based URL or UriInterface instance
     * @param int $status HTTP status code. Defaults to `302`
     * @return \Cake\Http\Response|null
     * @since 2.30.0
     */
    public function redirectMatchingReferer($url, int $status = 302): ?Response
    {
        if (is_array($url)) {
            $referer = $this->getRequest()->getSession()->read('referer');
            $expectedRedirect = '/' . ltrim(Router::url($url, true), Router::url('/', true));
            if ($referer && str_starts_with($referer, $expectedRedirect)) {
                $url = $referer;
            }
        }

        return $this->redirect($url, $status);
    }

    /**
     * Internal method to set an upload error.
     *
     * It saves the error as view var that `JsonView` should serialize and sets the response status code to 500.
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
