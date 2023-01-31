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
 * @property class-string<\Cake\View\Helper> $originClassName
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
     * Get magic method.
     *
     * It provides access to the cached properties of the test.
     * @param string $name Property name
     * @return mixed
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function __get(string $name)
    {
        //Rewrites the parent method
        if ($name === 'Helper') {
            if (empty($this->_cache['Helper'])) {
                $methods = get_child_methods($this->originClassName);
                $Helper = $this->getMockForHelper($this->originClassName, $methods);
                $OriginalHelper = new $this->originClassName($Helper->getView());
                $HtmlHelper = new HtmlHelper($Helper->getView());

                //Each method returns its original value, but links are already built and returned as HTML string
                foreach ($methods as $method) {
                    $Helper->method($method)->willReturnCallback(function () use ($OriginalHelper, $HtmlHelper, $method): array {
                        $result = $OriginalHelper->$method();

                        if (!empty($result[0])) {
                            $result[0] = implode('', array_map(fn(array $link): string => $HtmlHelper->link(...$link), $result[0]));
                        }

                        return $result;
                    });
                }

                $this->_cache['Helper'] = $Helper;
            }

            return $this->_cache['Helper'];
        }

        return parent::__get($name);
    }
}
