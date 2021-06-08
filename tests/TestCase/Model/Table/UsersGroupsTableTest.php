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

namespace MeCms\Test\TestCase\Model\Table;

use MeCms\Model\Validation\UsersGroupValidator;
use MeCms\TestSuite\TableTestCase;

/**
 * UsersGroupsTableTest class
 */
class UsersGroupsTableTest extends TableTestCase
{
    /**
     * @var \MeCms\Model\Table\UsersGroupsTable
     */
    protected $Table;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test for `buildRules()` method
     * @test
     */
    public function testBuildRules(): void
    {
        $example = ['name' => 'group', 'label' => 'Group label'];

        $entity = $this->Table->newEntity($example);
        $this->assertNotEmpty($this->Table->save($entity));

        //Saves again the same entity
        $entity = $this->Table->newEntity($example);
        $this->assertFalse($this->Table->save($entity));
        $this->assertEquals([
            'name' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
            'label' => ['_isUnique' => I18N_VALUE_ALREADY_USED],
        ], $entity->getErrors());
    }

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize(): void
    {
        $this->assertEquals('users_groups', $this->Table->getTable());
        $this->assertEquals('label', $this->Table->getDisplayField());
        $this->assertEquals('id', $this->Table->getPrimaryKey());

        $this->assertHasMany($this->Table->Users);
        $this->assertEquals('group_id', $this->Table->Users->getForeignKey());

        $this->assertHasBehavior('Timestamp');

        $this->assertInstanceOf(UsersGroupValidator::class, $this->Table->getValidator());
    }
}
