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
 * @since       2.25.4
 */
namespace MeCms\TestSuite;

use MeCms\TestSuite\TestCase;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * Abstract class for test entities
 */
abstract class CellTestCase extends TestCase
{
    use MockTrait;

    /**
     * Entity instance
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Widget;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Called before every test method
     * @return void
     * @uses $Widget
     * @uses $autoInitializeClass
     */
    public function setUp()
    {
        parent::setUp();

        if (!$this->Widget && $this->autoInitializeClass) {
            $this->Widget = $this->getMockBuilder(WidgetHelper::class)
                ->setMethods(null)
                ->setConstructorArgs([new View])
                ->getMock();
        }
    }
}
