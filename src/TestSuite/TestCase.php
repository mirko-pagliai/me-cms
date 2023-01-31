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
 * @since       2.26.0
 */

namespace MeCms\TestSuite;

use Cake\Core\Configure;
use MeCms\Model\Table\AppTable;
use MeTools\TestSuite\TestCase as BaseTestCase;

/**
 * TestCase class
 * @method \MeCms\Model\Table\AppTable getTable(string $alias, array $options = [])
 * @property \MeCms\Model\Table\AppTable $Table
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var array
     */
    protected array $_cache;

    /**
     * Constructor
     * @param string|null $name Test name
     * @param array $data Test data
     * @param int|string $dataName Test data name
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (!empty($data['cache'])) {
            $this->_cache = $data['cache'];
            unset($data['cache']);
        }
    }

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
        if ($name === 'Table') {
            if (empty($this->_cache['Table'])) {
                $className = $this->getTableClassNameFromAlias($this->alias);
                $this->_cache['Table'] = $this->getTable($this->alias, compact('className'));
            }

            return $this->_cache['Table'];
        }

        return parent::__get($name);
    }

    /**
     * Called after every test method
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->_cache['Table']) && $this->_cache['Table'] instanceof AppTable) {
            $this->_cache['Table']->clearCache();
        }

        $this->getTableLocator()->clear();
    }

    /**
     * Skips the test if the CakePHP version is less than the one being compared
     * @param string $version Version of CakePHP to compare
     * @param string $message The message to display
     * @return bool
     * @since 2.30.4-RC1
     */
    protected function skipIfCakeIsLessThan(string $version, string $message = ''): bool
    {
        return $this->skipIf(version_compare(Configure::version(), $version, '<'), $message);
    }
}
