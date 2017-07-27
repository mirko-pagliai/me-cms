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
 * @since       2.20.1
 */
namespace MeCms\TestSuite;

use MeCms\TestSuite\Traits\AuthMethodsTrait;
use MeTools\TestSuite\IntegrationTestCase as BaseIntegrationTestCase;

/**
 * A test case class intended to make integration tests of your controllers
 *  easier.
 *
 * This test class provides a number of helper methods and features that make
 *  dispatching requests and checking their responses simpler. It favours full
 *  integration tests over mock objects as you can test more of your code
 *  easily and avoid some of the maintenance pitfalls that mock objects create.
 */
class IntegrationTestCase extends BaseIntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        //Sets key for cookies
        $controller->Cookie->config('key', 'somerandomhaskeysomerandomhaskey');
    }
}
