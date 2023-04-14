<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
use MeCms\View\Helper\IdentityHelper;
use MeTools\TestSuite\HelperTestCase;

/**
 * IdentityHelperTest class
 * @property \MeCms\View\Helper\IdentityHelper $Helper
 */
class IdentityHelperTest extends HelperTestCase
{
    /**
     * @test
     * @uses \MeCms\View\Helper\IdentityHelper::isGroup()
     */
    public function testIsGroup(): void
    {
        $Request = new ServerRequest();
        $Request = $Request->withAttribute('identity', new Identity(['group' => ['name' => 'manager']]));
        $IdentityHelper = new IdentityHelper(new View($Request));
        $this->assertTrue($IdentityHelper->isGroup('manager'));
        $this->assertTrue($IdentityHelper->isGroup('manager', 'admin'));
        $this->assertFalse($IdentityHelper->isGroup('admin'));
        $this->assertFalse($IdentityHelper->isGroup('user'));
        $this->assertFalse($IdentityHelper->isGroup('admin', 'user'));

        $this->expectExceptionMessage('`group.name` path is missing');
        $Request = $Request->withAttribute('identity', new Identity([]));
        $IdentityHelper = new IdentityHelper(new View($Request));
        $this->assertFalse($IdentityHelper->isGroup('user'));
    }
}
