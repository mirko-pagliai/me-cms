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

use Cake\Http\ServerRequest;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * Abstract class for test cells
 * @property \MeCms\View\Helper\WidgetHelper $Widget
 * @todo renamed as `WidgetTestCase`
 */
abstract class CellTestCase extends TestCase
{
    /**
     * Get magic method.
     *
     * It provides access to the cached properties of the test.
     * @param string $name Property name
     * @return mixed
     * @throws \ReflectionException
     */
    public function __get(string $name)
    {
        switch ($name) {
            //Rewrites the parent's method
            case 'alias':
                if (empty($this->_cache['alias'])) {
                    $this->_cache['alias'] = substr($this->getAlias($this), 0, -7);
                }

                return $this->_cache['alias'];
            //Rewrites the parent's method
            case 'Table':
                if (empty($this->_cache['Table'])) {
                    $className = $this->getTableClassNameFromAlias($this->alias);
                    $this->_cache['Table'] = $this->getTable($this->alias, compact('className'));
                }

                return $this->_cache['Table'];
            case 'Widget':
                if (empty($this->_cache['Widget'])) {
                    $this->_cache['Widget'] = new WidgetHelper(new View((new ServerRequest())->withEnv('REQUEST_URI', '/')));
                }

                return $this->_cache['Widget'];
        }

        return parent::__get($name);
    }
}
