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

use Authentication\Identity;
use Cake\View\Helper;
use MeTools\TestSuite\HelperTestCase;
use MeTools\View\Helper\HtmlHelper;

/**
 * Abstract class for test `MenuHelper` classes
 * @property class-string<\MeCms\View\Helper\AbstractMenuHelper> $originClassName
 */
abstract class MenuHelperTestCase extends HelperTestCase
{
    /**
     * @var \MeCms\View\Helper\AbstractMenuHelper&\PHPUnit\Framework\MockObject\MockObject
     */
    protected Helper $Helper;

    /**
     * Called before every test method
     * @return void
     * @throws \ErrorException
     */
    public function setUp(): void
    {
        parent::setUp();

        if (empty($this->Helper)) {
            $methodsToMock = get_child_methods($this->originClassName);
            /** @var \MeCms\View\Helper\AbstractMenuHelper&\PHPUnit\Framework\MockObject\MockObject $Helper */
            $Helper = $this->getMockForHelper($this->originClassName, $methodsToMock);

            $OriginalHelper = new $this->originClassName($Helper->getView());
            $HtmlHelper = new HtmlHelper($Helper->getView());

            //Each method returns its original value, but links (the first result value) are already built and returned as HTML string
            foreach ($methodsToMock as $method) {
                $Helper->method($method)->willReturnCallback(function () use ($OriginalHelper, $HtmlHelper, $method) {
                    $result = $OriginalHelper->$method();

                    return empty($result) ? [] : [implode('', array_map(fn(array $link): string => $HtmlHelper->link(...$link), $result[0]))] + $result;
                });
            }

            $this->Helper = $Helper;
        }

        $this->setIdentity(['group' => ['name' => 'user']]);
    }

    /**
     * Internal method to set the identity for the current helper
     * @param array $data Identity data
     * @return void
     */
    protected function setIdentity(array $data = []): void
    {
        $Request = $this->Helper->getView()->getRequest()->withAttribute('identity', new Identity($data));
        $this->Helper->getView()->setRequest($Request);
        if ($this->Helper->Identity) {
            $this->Helper->Identity->initialize([]);
        }
    }
}
