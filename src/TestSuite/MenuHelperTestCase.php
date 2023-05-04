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

namespace MeCms\TestSuite;

use Authentication\Identity;
use MeTools\TestSuite\HelperTestCase;
use MeTools\View\Helper\HtmlHelper;

/**
 * Abstract class for test `MenuHelper` classes
 * @property \MeCms\View\Helper\AbstractMenuHelper $Helper
 */
abstract class MenuHelperTestCase extends HelperTestCase
{
    /**
     * @var \MeTools\View\Helper\HtmlHelper
     */
    protected HtmlHelper $Html;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setIdentity(['group' => ['name' => 'user']]);

        $this->Html ??= new HtmlHelper($this->Helper->getView());
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

        if (property_exists($this->Helper, 'Identity')) {
            $this->Helper->Identity->initialize([]);
        }
    }

    /**
     * Internal method to get links as html.
     *
     * Returns an array where each link has been transformed into a html string.
     * @return string[]
     */
    protected function getLinksAsHtml(): array
    {
        return array_map(fn(array $link): string => call_user_func_array([$this->Html, 'link'], $link), $this->Helper->getLinks());
    }

    /**
     * Test for `getLinks()` method.
     *
     * You have implement this method.
     * @return void
     * @test
     */
    abstract public function testGetLinks(): void;

    /**
     * Test for `getOptions()` method
     * @return void
     * @test
     */
    public function testGetOptions(): void
    {
        $result = $this->Helper->getOptions();
        $this->assertIsArrayNotEmpty($result);
        $this->assertArrayHasKey('icon', $result);
    }

    /**
     * Test for `getTitle()` method
     * @return void
     * @test
     */
    public function testGetTitle(): void
    {
        $this->assertIsString($this->Helper->getTitle());
    }
}
