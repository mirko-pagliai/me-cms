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
     * @return void
     */
    public function assertBelongsTo(Association $association): void
    {
        $this->assertInstanceOf(BelongsTo::class, $association);
    }

    /**
     * Asserts that the table has a "belongs to many" association
     * @param \Cake\ORM\Association $association Association
     * @return void
     * @todo missing params
     */
    public function assertBelongsToMany(Association $association): void
    {
        $this->assertInstanceOf(BelongsToMany::class, $association);
    }

    /**
     * Asserts that the table has a behavior
     * @param string|array $behavior Behavior name as string or array
     * @return void
     * @todo missing params
     */
    public function assertHasBehavior($behavior): void
    {
        foreach ((array)$behavior as $name) {
            $this->assertTrue($this->Table->hasBehavior($name));
        }
    }

    /**
     * Asserts that the table has a "many" association
     * @param \Cake\ORM\Association $association Association
     * @return void
     * @todo missing params
     */
    public function assertHasMany(Association $association): void
    {
        $this->assertInstanceOf(HasMany::class, $association);
    }
}
