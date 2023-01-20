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

use Cake\ORM\Entity;

/**
 * Abstract class for test entities
 */
abstract class EntityTestCase extends TestCase
{
    /**
     * @var \Cake\ORM\Entity
     */
    protected Entity $Entity;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected bool $autoInitializeClass = true;

    /**
     * Asserts that the entity has a "no accessible" property
     * @param string|array $property Property name
     * @return void
     */
    public function assertHasNoAccessibleProperty($property): void
    {
        !empty($this->Entity) ?: $this->fail('The property `$this->Entity` has not been set');

        foreach ((array)$property as $name) {
            $this->assertFalse($this->Entity->isAccessible($name));
        }
    }

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->Entity) && $this->autoInitializeClass) {
            /** @var \Cake\ORM\Entity $Entity */
            $Entity = new ($this->getOriginClassNameOrFail($this))();
            $this->Entity = $Entity;
        }
    }
}
