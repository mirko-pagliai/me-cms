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
 * @since       2.20.0
 */

namespace MeCms\TestSuite;

use Cake\Utility\Inflector;

/**
 * Abstract class for test validation classes
 * @property \MeCms\Model\Table\AppTable $Table
 */
abstract class ValidationTestCase extends TestCase
{
    /**
     * @var array
     */
    protected array $example;

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
                    $this->_cache['alias'] = Inflector::pluralize($this->getAlias($this));
                }

                return $this->_cache['alias'];
            case 'Table':
                if (empty($this->_cache['Table'])) {
                    $className = $this->getTableClassNameFromAlias($this->alias);
                    $this->_cache['Table'] = $this->getTable($this->alias, compact('className'));
                }

                return $this->_cache['Table'];
        }

        return parent::__get($name);
    }

    /**
     * Assert that all data are required.
     *
     * It first verifies that the entity, compiled with passed data, has no errors.
     *
     * Then it removes one field at a time, verifying that the "this field is required" error is generated.
     * @param array $data Valid data
     * @param string[] $exclude Key to be excluded
     * @return void
     */
    public function assertAllDataAreRequired(array $data, array $exclude = [])
    {
        $this->example ?: $this->fail('The property `$this->example` has not been set');

        $this->assertEmpty($this->Table->newEntity($this->example)->getErrors());

        foreach (array_keys($data) as $key) {
            if (in_array($key, $exclude)) {
                continue;
            }

            //Create a copy of the example data and removes the current value
            $copy = $data;
            unset($copy[$key]);

            $expectedErrors = [$key => ['_required' => 'This field is required']];
            $this->assertEquals($expectedErrors, $this->Table->newEntity($copy)->getErrors());
        }
    }

    /**
     * Test validation.
     * It tests the proper functioning of the example data.
     * @return void
     * @test
     */
    public function testAllDataAreRequired(): void
    {
        $this->assertAllDataAreRequired($this->example);
    }
}
