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
 * @since       2.31.7
 */

namespace MeCms\Controller\Component;

use Authentication\Authenticator\AuthenticatorInterface;
use Authentication\Controller\Component\AuthenticationComponent as BaseAuthenticationComponent;
use Authorization\Identity;
use Cake\Event\EventInterface;

/**
 * Controller Component for interacting with Authentication.
 *
 * This class just adds the `afterIdentify` event.
 */
class AuthenticationComponent extends BaseAuthenticationComponent
{
    /**
     * Get the Controller callbacks this Component is interested in
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return ['Authentication.afterIdentify' => 'afterIdentify'] + parent::implementedEvents();
    }

    /**
     * `afterIdentify` event
     * @param \Cake\Event\EventInterface $Event Event
     * @param \Authentication\Authenticator\AuthenticatorInterface $Provider Provider
     * @param \Authorization\Identity $Identity Identity
     * @return void
     */
    public function afterIdentify(EventInterface $Event, AuthenticatorInterface $Provider, Identity $Identity): void
    {
        /** @var \MeCms\Controller\AppController $Controller */
        $Controller = $this->getController();
        $Controller->LoginRecorder->setConfig('user', $Identity->getIdentifier())->write();
    }
}
