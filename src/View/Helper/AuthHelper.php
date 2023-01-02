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

namespace MeCms\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use MeCms\AuthTrait;

/**
 * Auth Helper.
 *
 * Allows you to check the user data.
 * @deprecated 2.31.0 Use instead the `IdentityHelper`
 */
class AuthHelper extends Helper
{
    use AuthTrait;

    /**
     * Default Constructor
     * @param \Cake\View\View $view The View this helper is being attached to
     * @param array<string, mixed> $config Configuration settings for the helper
     */
    public function __construct(View $view, array $config = [])
    {
        deprecationWarning('Deprecated. Use instead the `IdentityHelper`');

        parent::__construct($view, $config);
    }

    /**
     * Constructor hook method
     * @param array $config The configuration settings provided to this helper
     * @return void
     */
    public function initialize(array $config): void
    {
        $config += ['user' => $this->getView()->getRequest()->getSession()->read('Auth.User')];
        $this->setConfig($config);
    }

    /**
     * Get the current user from storage
     * @param string|null $key Field to retrieve or `null`
     * @return mixed Either User record or `null` if no user is logged in, or retrieved field if key is specified
     */
    public function user(?string $key = null)
    {
        return $key ? $this->getConfig('user.' . $key) : $this->getConfig('user');
    }
}
