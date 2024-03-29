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
use Cake\Event\EventInterface;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use RuntimeException;

/**
 * Application controller class
 * @property \MeCms\Controller\Component\AuthenticationComponent $Authentication
 * @property \MeTools\Controller\Component\FlashComponent $Flash
 * @property \MeCms\Controller\Component\LoginRecorderComponent $LoginRecorder
 * @property \Recaptcha\Controller\Component\RecaptchaComponent $Recaptcha
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
        parent::beforeFilter($event);

        //Checks if the site is offline
        if ($this->getRequest()->is('offline')) {
            return $this->redirect(['_name' => 'offline']);
        }

        //Checks if the user's IP address is reported as spammer
        if ($this->isSpammer()) {
            return $this->redirect(['_name' => 'ipNotAllowed']);
        }

        //Sets paginate limit and maximum paginate limit
        //See http://book.cakephp.org/4.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        $this->paginate['limit'] = $this->paginate['maxLimit'] = getConfigOrFail('default.records');

        $this->viewBuilder()->setClassName('MeCms.View/App');
    }

    /**
     * Called after the controller action is run, but before the view is rendered
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);

        //Layout for ajax and json requests
        if ($this->getRequest()->is(['ajax', 'json'])) {
            $this->viewBuilder()->setLayout('MeCms.ajax');
        }
    }

    /**
     * Returns the `page` value from the query
     * @return string
     * @since 2.31.1
     */
    public function getQueryPage(): string
    {
        $queryPage = $this->getRequest()->getQuery('page');

        return trim(is_string($queryPage) && is_positive($queryPage) ? $queryPage : '1', '/');
    }

    /**
     * Gets the `paging` request attribute and parameter
     * @return array<string, mixed>
     * @since 2.27.1
     */
    public function getPaging(): array
    {
        return $this->getRequest()->getAttribute('paging') ?? $this->getRequest()->getParam('paging', []);
    }

    /**
     * Initialization hook method
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        $this->loadComponent('RequestHandler', ['enableBeforeRedirect' => false]);
        $this->loadComponent('MeTools.Flash');
        $this->loadComponent('MeCms.LoginRecorder');
        $this->loadComponent('MeCms.Authentication', [
            'identityCheckEvent' => 'Controller.initialize',
            'unauthenticatedMessage' => __d('me_cms', 'You are not authorized for this action'),
            'logoutRedirect' => Router::url(['_name' => 'homepage']),
        ]);

        //Loads Recaptcha. Throws an exception if the keys are not set or are the default ones
        if (getConfig('MeCms.security.recaptcha')) {
            try {
                [$sitekey, $secret] = array_values(getConfigOrFail('Recaptcha'));
                if ($sitekey == 'your-public-key-here' || $secret == 'your-public-key-here') {
                    throw new RuntimeException();
                }
            } catch (RuntimeException $e) {
                throw new RuntimeException('Missing Recaptcha keys. You can rename the `config/recaptcha.example.php` file as `recaptcha.php` and change the keys');
            }

            $this->loadComponent('Recaptcha.Recaptcha', compact('sitekey', 'secret') + ['lang' => substr(I18n::getLocale(), 0, 2)]);
        }

        //By default, "unprefixed" actions do not require authentication and identity
        $this->Authentication->setConfig('requireIdentity', false);

        parent::initialize();
    }

    /**
     * Checks if the user's IP address is reported as a spammer
     * @return bool
     * @since 2.15.2
     */
    protected function isSpammer(): bool
    {
        return $this->getRequest()->is('spammer') && !$this->getRequest()->is('action', 'ipNotAllowed', 'Systems');
    }

    /**
     * Sets the `paging` request attribute and parameter
     * @param array[] $paging Paging value
     * @return $this
     * @since 2.29.1
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setPaging(array $paging)
    {
        return $this->setRequest($this->request->withAttribute('paging', $paging)->withParam('paging', $paging));
    }
}
