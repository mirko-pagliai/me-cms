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

/**
 * Admin Application controller class
 */
abstract class AppController extends BaseAppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An EventInterface instance
     * @return \Cake\Http\Response|null|void
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

        //Saves the referer in session, excluding post and put requests
        $request = $this->getRequest();
        $referer = $request->referer();
        if (!$request->is('post') && !$request->is('put') && $referer && $request->getRequestTarget() !== $referer) {
            $request->getSession()->write('referer', $referer);
        }

        return null;
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
     * Redirects to given $url, after turning off $this->autoRender.
     *
     * Unlike the common `redirect()` method, checks whether a referer has been
     *  saved in the session that coincides with the requested redirect (and
     * which could contain a query string, for example).
     * @param string|array|\Psr\Http\Message\UriInterface $url A string,
     *  array-based URL or UriInterface instance
     * @param int $status HTTP status code. Defaults to `302`
     * @return \Cake\Http\Response|null
     * @since 2.30.0
     */
    public function redirectMatchingReferer($url, int $status = 302): ?Response
    {
        if (is_array($url)) {
            $referer = $this->getRequest()->getSession()->read('referer');
            $expectedRedirect = '/' . ltrim(Router::url($url, true), Router::url('/', true));
            if ($referer && string_starts_with($referer, $expectedRedirect)) {
                $url = $referer;
            }
        }

        return $this->redirect($url, $status);
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
