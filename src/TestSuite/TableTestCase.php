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

use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;

/**
 * Abstract class for test tables
 */
abstract class TableTestCase extends TestCase
{
    /**
     * Get magic method.
     *
     * It provides access to the cached properties of the test.
     * @param string $name Property name
     * @return \MeCms\Model\Table\AppTable|mixed
     * @throws \ReflectionException
     */
    public function __get(string $name)
    {
        if ($name === 'Table') {
            if (empty($this->_cache['Table'])) {
                $this->_cache['Table'] = new $this->originClassName();
            }

            return $this->_cache['Table'];
        }

        return parent::__get($name);
    }

    /**
     * Asserts that the table has a "belongs to" association
     * @param \Cake\ORM\Association $association Association
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertBelongsTo(Association $association, string $message = ''): void
    {
        $message = $message ?: sprintf('Failed asserting that `%s` is an instance of `%s`', $association->getClassName(), BelongsTo::class);
        $this->assertInstanceOf(BelongsTo::class, $association, $message);
    }

    /**
     * Asserts that the table has a "belongs to many" association
     * @param \Cake\ORM\Association $association Association
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertBelongsToMany(Association $association, string $message = ''): void
    {
        $message = $message ?: sprintf('Failed asserting that `%s` is an instance of `%s`', $association->getClassName(), BelongsToMany::class);
        $this->assertInstanceOf(BelongsToMany::class, $association, $message);
    }

    /**
     * Asserts that the table has a behavior
     * @param string|string[] $behavior Behavior name as string or array
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertHasBehavior($behavior, string $message = ''): void
    {
        foreach ((array)$behavior as $name) {
            $this->assertTrue($this->Table->hasBehavior($name), $message ?: sprintf('Failed asserting that `%s` has `%s` behavior', $this->originClassName, $name));
        }
    }

    /**
     * Asserts that the table has a "many" association
     * @param \Cake\ORM\Association $association Association
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertHasMany(Association $association, string $message = ''): void
    {
        $message = $message ?: sprintf('Failed asserting that `%s` is an instance of `%s`', $association->getClassName(), HasMany::class);
        $this->assertInstanceOf(HasMany::class, $association, $message);
    }
}
