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

namespace MeCms\Controller\Component;

use Cake\Controller\Component\AuthComponent as CakeAuthComponent;
use Cake\Controller\ComponentRegistry;
use MeCms\AuthTrait;

/**
 * Authentication control component class.
 *
 * Binds access control with user authentication and session management.
 * @deprecated 2.31.0 Use instead the `AuthenticationComponent`
 */
class AuthComponent extends CakeAuthComponent
{
    use AuthTrait;

    /**
     * Constructor
     * @param \Cake\Controller\ComponentRegistry $registry A component registry
     * @param array<string, mixed> $config Array of configuration settings
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        deprecationWarning('Deprecated. Use instead the `AuthenticationComponent`');

        parent::__construct($registry, $config);
    }

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this component
     * @return void
     */
    public function initialize(array $config): void
    {
        $config += [
            'authenticate' => [
                'Form' => [
                    'finder' => 'auth',
                    'userModel' => 'MeCms.Users',
                ],
            ],
            'authorize' => 'Controller',
            'flash' => [
                'element' => 'MeTools.flash',
                'params' => ['class' => 'alert-danger'],
            ],
            'loginAction' => ['_name' => 'login'],
            'loginRedirect' => ['_name' => 'dashboard'],
            'logoutRedirect' => ['_name' => 'homepage'],
            'unauthorizedRedirect' => ['_name' => 'dashboard'],
        ];

        //The authorization error is shown only if the user is already logged
        //  in and is trying to do something not allowed
        $config += ['authError' => $this->user('id') ? __d('me_cms', 'You are not authorized for this action') : false];

        parent::initialize($config);
        $this->setConfig($config);
    }
}
