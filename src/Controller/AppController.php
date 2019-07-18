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
namespace MeCms\Controller;

use App\Controller\AppController as BaseAppController;
use Cake\Event\Event;
use Cake\I18n\I18n;

/**
 * Application controller class
 */
class AppController extends BaseAppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Http\Response|null|void
     * @uses isSpammer()
     */
    public function beforeFilter(Event $event)
    {
        //Checks if the site is offline
        if ($this->getRequest()->isOffline()) {
            return $this->redirect(['_name' => 'offline']);
        }

        //Checks if the user's IP address is reported as spammer
        if ($this->isSpammer()) {
            return $this->redirect(['_name' => 'ipNotAllowed']);
        }

        $this->viewBuilder()->setClassName('MeCms.View/App');

        //Sets the paginate limit and the maximum paginate limit
        //See http://book.cakephp.org/4.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = getConfigOrFail('default.records');

        if ($this->getRequest()->isAdmin()) {
            $this->viewBuilder()->setClassName('MeCms.View/Admin');

            $this->paginate['limit'] = getConfigOrFail('admin.records');
        } else {
            //Authorizes the current action
            $this->Auth->allow();
        }

        $this->paginate['maxLimit'] = $this->paginate['limit'];

        //Layout for ajax and json requests
        if ($this->getRequest()->is(['ajax', 'json'])) {
            $this->viewBuilder()->setLayout('MeCms.ajax');
        }

        parent::beforeFilter($event);
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        //Loads components
        //The configuration for `AuthComponent`  takes place in the same class
        $this->loadComponent('MeCms.Auth');
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('MeTools.Uploader');
        $this->loadComponent('Recaptcha.Recaptcha', [
            'sitekey' => getConfigOrFail('Recaptcha.public'),
            'secret' => getConfigOrFail('Recaptcha.private'),
            'lang' => substr(I18n::getLocale(), 0, 2),
        ]);

        parent::initialize();
    }

    /**
     * Checks if the user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null): bool
    {
        //Only admin and managers can access admin actions
        if ($this->getRequest()->isAdmin()) {
            return $this->Auth->isGroup(['admin', 'manager']);
        }

        //Any registered user can access actions without prefix. Default deny
        return !$this->getRequest()->getParam('prefix');
    }

    /**
     * Checks if the user's IP address is reported as a spammer
     * @return bool
     * @since 2.15.2
     */
    protected function isSpammer(): bool
    {
        return $this->getRequest()->isSpammer() && !$this->getRequest()->isAction('ipNotAllowed', 'Systems');
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
        $this->response = $this->response->withStatus(500);

        $this->set(compact('error'));
        $this->set('_serialize', ['error']);
    }
}
