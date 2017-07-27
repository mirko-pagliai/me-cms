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
 */
namespace MeCms\Test\TestCase\Model\Entity;

use MeCms\Model\Entity\UsersGroup;
use MeTools\TestSuite\TestCase;

/**
 * UsersGroupTest class
 */
class UsersGroupTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\UsersGroup
     */
    protected $UsersGroup;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->UsersGroup = new UsersGroup;
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->UsersGroup->isAccessible('id'));
        $this->assertFalse($this->UsersGroup->isAccessible('user_count'));
        $this->assertFalse($this->UsersGroup->isAccessible('modified'));
    }
}
