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

namespace MeCms\Test\TestCase\View\Helper;

use Authentication\Identity;
use Cake\Http\ServerRequest;
use Cake\View\View;
use MeCms\Model\Entity\User;
use MeCms\View\Helper\IdentityHelper;
use MeTools\TestSuite\HelperTestCase;

/**
 * IdentityHelperTest class
 * @property \MeCms\View\Helper\IdentityHelper $Helper
 */
class IdentityHelperTest extends HelperTestCase
{
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
     * @test
     * @uses \MeCms\View\Helper\IdentityHelper::isGroup()
     */
    public function testIsGroup(): void
    {
        /** @var \MeCms\Model\Table\UsersTable $UsersTable */
        $UsersTable = $this->getTable('MeCms.Users');
        /** @var \MeCms\Model\Entity\User $User */
        $User = $UsersTable->findByGroupId(2)->contain(['Groups' => ['fields' => ['name']]])->firstOrFail();
        $this->User = $User;

        $Request = $this->createPartialMock(ServerRequest::class, ['getAttribute']);
        $Request->method('getAttribute')->with('identity')->willReturnCallback(fn(): Identity => new Identity($this->User->toArray()));

        $IdentityHelper = new IdentityHelper(new View($Request));
        $IdentityHelper->initialize([]);

        $this->assertTrue($IdentityHelper->isGroup('manager'));
        $this->assertTrue($IdentityHelper->isGroup('manager', 'admin'));
        $this->assertFalse($IdentityHelper->isGroup('admin'));
        $this->assertFalse($IdentityHelper->isGroup('user'));
        $this->assertFalse($IdentityHelper->isGroup('admin', 'user'));
    }
}
