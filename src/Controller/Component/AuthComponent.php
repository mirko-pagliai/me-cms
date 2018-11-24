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
 * @see         http://api.cakephp.org/3.4/class-Cake.Controller.Component.AuthComponent.html
 */
namespace MeCms\Controller\Component;

use Cake\Controller\Component\AuthComponent as CakeAuthComponent;

/**
 * Authentication control component class.
 *
 * Binds access control with user authentication and session management.
 */
class AuthComponent extends CakeAuthComponent
{
    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this
     *  component
     * @return void
     */
    public function initialize(array $config)
    {
        $defaultConfig = [
            'authenticate' => [
                'Form' => [
                    'finder' => 'auth',
                    'userModel' => 'MeCms.Users',
                ],
            ],
            'authError' => __d('me_cms', 'You are not authorized for this action'),
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
        //  in and he is trying to do something not allowed
        if (!$this->user('id')) {
            $defaultConfig['authError'] = false;
        }

        $config = array_merge($defaultConfig, $config);

        $this->setConfig($config);

        parent::initialize($config);
    }

    /**
     * Checks whether the logged user has a specific ID.
     *
     * You can pass the ID as string or array of IDS.
     * In the last case, it will be sufficient that the user has one of the IDS.
     * @param string|array $id User ID as string or array
     * @return bool
     */
    public function hasId($id)
    {
        return $this->user('id') ? in_array($this->user('id'), (array)$id) : false;
    }

    /**
     * Checks whether the logged user is the admin founder (ID 1)
     * @return bool
     */
    public function isFounder()
    {
        return $this->user('id') ? $this->user('id') === 1 : false;
    }

    /**
     * Checks whether the user is logged in
     * @return bool
     */
    public function isLogged()
    {
        return (bool)$this->user('id');
    }

    /**
     * Checks whether the logged user belongs to a group.
     *
     * You can pass the group as string or array of groups.
     * In the last case, it will be sufficient that the user belongs to one of
     *  the groups.
     * @param string|array $group User group as string or array
     * @return bool
     */
    public function isGroup($group)
    {
        return $this->user('group.name') ? in_array($this->user('group.name'), (array)$group) : false;
    }
}
