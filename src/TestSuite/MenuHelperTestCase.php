<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use Authentication\Identity;
use MeTools\TestSuite\HelperTestCase;
use MeTools\View\Helper\HtmlHelper;

/**
 * Abstract class for test `MenuHelper` classes
 * @property \MeCms\View\Helper\MenuHelper&\PHPUnit\Framework\MockObject\MockObject $Helper
 */
abstract class MenuHelperTestCase extends HelperTestCase
{
    /**
     * Internal method to set the identity for the current helper
     * @param array $data Identity data
     * @return void
     */
    protected function setIdentity(array $data = []): void
    {
        $Request = $this->Helper->getView()->getRequest()->withAttribute('identity', new Identity($data));
        $this->Helper->getView()->setRequest($Request);
        $this->Helper->Identity->initialize([]);
    }

    /**
     * Called before every test method
     * @return void
     * @throws \ErrorException
     */
    protected function setUp(): void
    {
        if (empty($this->Helper)) {
            /**
             * @var class-string<\Cake\View\Helper> $className
             * @noinspection PhpRedundantVariableDocTypeInspection
             */
            $className = $this->getOriginClassName($this);
            $methods = get_child_methods($className);

            //Mock: each method returns its original value, but the links are already built and returned as HTML string
            /** @var \MeCms\View\Helper\MenuHelper&\PHPUnit\Framework\MockObject\MockObject $Helper */
            $Helper = $this->getMockForHelper($className, $methods);

            $OriginalHelper = new $className($Helper->getView());
            $HtmlHelper = new HtmlHelper($Helper->getView());
            foreach ($methods as $method) {
                $Helper->method($method)->willReturnCallback(function () use ($method, $OriginalHelper, $HtmlHelper): array {
                    $returned = $OriginalHelper->$method();

                    if (!empty($returned[0])) {
                        $returned[0] = implode(PHP_EOL, array_map(fn(array $link): string => call_user_func_array([$HtmlHelper, 'link'], $link), $returned[0]));
                    }

                    return $returned;
                });
            }

            $this->Helper = $Helper;
        }

        parent::setUp();
    }
}
