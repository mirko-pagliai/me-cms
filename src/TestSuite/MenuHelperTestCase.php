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
 * @since       2.28.0
 */

namespace MeCms\TestSuite;

use MeTools\TestSuite\HelperTestCase;
use MeTools\View\Helper\HtmlHelper;

/**
 * Abstract class for test `MenuHelper` classes
 * @property \MeCms\View\Helper\MenuHelper&\PHPUnit\Framework\MockObject\MockObject $Helper
 */
abstract class MenuHelperTestCase extends HelperTestCase
{
    /**
     * Internal method to write auth data on session
     * @param array $data Data you want to write
     * @return void
     */
    protected function writeAuthOnSession(array $data = []): void
    {
        $this->Helper->getView()->getRequest()->getSession()->write('Auth.User', $data);
        $this->Helper->Auth->initialize([]);
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        $className = $this->getOriginClassNameOrFail($this);
        $methods = get_child_methods($className);

        //Mocks the helper. Each method returns its original value, but the
        //  links are already builded and returned as an HTML string
        /** @var \MeCms\View\Helper\MenuHelper&\PHPUnit\Framework\MockObject\MockObject $Helper */
        $Helper = $this->getMockForHelper($className, $methods);

        foreach ($methods as $method) {
            $Helper->method($method)->will($this->returnCallback(function () use ($className, $method) {
                $originalHelper = new $className($this->Helper->getView());
                $returned = $originalHelper->$method();

                if (!empty($returned[0])) {
                    $HtmlHelper = new HtmlHelper($this->Helper->getView());
                    $returned[0] = implode(PHP_EOL, array_map(function (array $link) use ($HtmlHelper) {
                        return call_user_func_array([$HtmlHelper, 'link'], $link);
                    }, $returned[0]));
                }

                return $returned;
            }));
        }

        $this->Helper = $Helper;

        parent::setUp();
    }
}
