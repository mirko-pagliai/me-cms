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

namespace MeCms\Test\TestCase\Controller\Component;

use Authentication\Identity;
use Error;
use MeCms\Controller\Admin\UsersController;
use MeCms\Controller\Component\AuthenticationComponent;
use MeCms\Model\Entity\User;
use MeTools\TestSuite\ComponentTestCase;

/**
 * AuthenticationComponentTest class
 */
class AuthenticationComponentTest extends ComponentTestCase
{
    /**
     * @var \MeCms\Controller\Component\AuthenticationComponent&\PHPUnit\Framework\MockObject\MockObject
     */
    protected AuthenticationComponent $Component;

    /**
     * @var \MeCms\Model\Entity\User
     */
    protected User $User;

    /**
     * Fixtures
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var \MeCms\Model\Table\UsersTable $UsersTable */
        $UsersTable = $this->getTable('MeCms.Users');
        /** @var \MeCms\Model\Entity\User $User */
        $User = $UsersTable->findByGroupId(2)->contain(['Groups' => ['fields' => ['name']]])->firstOrFail();
        $this->User = $User;

        if (empty($this->Component)) {
            $this->Component = $this->createPartialMock(AuthenticationComponent::class, ['getController', 'getIdentity']);
            $this->Component->method('getController')->willReturn(new UsersController());
        }
        $this->Component->method('getIdentity')->willReturnCallback(fn(): Identity => new Identity($this->User->toArray()));
    }

    /**
     * @test
     * @uses \MeCms\Controller\Component\AuthenticationComponent::getId()
     */
    public function testGetId(): void
    {
        $this->assertSame($this->User->get('id'), $this->Component->getId());

        //On failure
        $this->expectException(Error::class);
        $this->User = $this->User->unset('id');
        $this->Component->getId();
    }

    /**
     * @test
     * @uses \MeCms\Controller\Component\AuthenticationComponent::isGroup()
     */
    public function testIsGroup(): void
    {
        $this->assertTrue($this->Component->isGroup('manager'));
        $this->assertTrue($this->Component->isGroup('manager', 'admin'));
        $this->assertFalse($this->Component->isGroup('admin'));
        $this->assertFalse($this->Component->isGroup('user'));
        $this->assertFalse($this->Component->isGroup('admin', 'user'));

        //On failure
        $this->expectExceptionMessage('`group.name` path is missing');
        $this->User = $this->User->unset('group');
        $this->Component->isGroup('admin');
    }
}
