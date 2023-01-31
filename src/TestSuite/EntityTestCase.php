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

use Cake\Utility\Inflector;

/**
 * Abstract class for test entities
 * @property \Cake\ORM\Entity $Entity
 */
abstract class EntityTestCase extends TestCase
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
            //Rewrites the parent method
            case 'alias':
                if (empty($this->_cache['alias'])) {
                    $this->_cache['alias'] = Inflector::pluralize(get_class_short_name($this->originClassName));
                }

                return $this->_cache['alias'];
            case 'Entity':
                if (empty($this->_cache['Entity'])) {
                    $this->_cache['Entity'] = new $this->originClassName();
                }

                return $this->_cache['Entity'];
        }

        return parent::__get($name);
    }

    /**
     * Asserts that the entity has a "no accessible" property
     * @param string|array $property Property name
     * @return void
     * @todo missing params
     */
    public function assertHasNoAccessibleProperty($property): void
    {
        foreach ((array)$property as $name) {
            $this->assertFalse($this->Entity->isAccessible($name));
        }
    }
}
