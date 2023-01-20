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
 * @since       2.25.4
 */

namespace MeCms\TestSuite;

use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * Abstract class for test cells
 */
abstract class CellTestCase extends TestCase
{
    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected WidgetHelper $Widget;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected bool $autoInitializeClass = true;

    /**
     * Called before every test method
     * @return void
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->Widget) && $this->autoInitializeClass) {
            $this->Widget = new WidgetHelper(new View());
        }

        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);

        if (empty($this->Table) && $this->autoInitializeClass) {
            $alias = substr($this->getAlias($this), 0, -7);
            $className = $this->getTableClassNameFromAlias($alias);
            if (class_exists($className)) {
                $this->Table = $this->getTable($alias, compact('className'));
            }
        }
    }
}
