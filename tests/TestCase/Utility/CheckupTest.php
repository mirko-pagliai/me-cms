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

namespace MeCms\Test\TestCase\Utility;

use MeCms\TestSuite\TestCase;
use MeCms\Utility\Checkup;
use MeCms\Utility\Checkups\Apache;
use MeCms\Utility\Checkups\Backups;
use MeCms\Utility\Checkups\KCFinder;
use MeCms\Utility\Checkups\PHP;
use MeCms\Utility\Checkups\Plugin;
use MeCms\Utility\Checkups\TMP;
use MeCms\Utility\Checkups\Webroot;

/**
 * CheckupTest class
 */
class CheckupTest extends TestCase
{
    /**
     * @var \MeCms\Utility\Checkup
     */
    protected $Checkup;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Checkup = new Checkup();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        @unlink_recursive(KCFINDER, 'empty');
    }

    /**
     * Test for `$Apache` property and methods from `Apache` class
     * @test
     */
    public function testApache()
    {
        $this->assertInstanceof(Apache::class, $this->Checkup->Apache);
        $this->assertEquals(['modules', 'getVersion'], get_class_methods($this->Checkup->Apache));
        $this->assertArrayKeysEqual(['expires', 'rewrite'], $this->Checkup->Apache->modules());
        $this->assertRegExp('/^[\d\.]+$/', $this->Checkup->Apache->getVersion());
    }

    /**
     * Test for `$Backups` property and methods from `Backups` class
     * @test
     */
    public function testBackups()
    {
        $this->assertInstanceof(Backups::class, $this->Checkup->Backups);
        $this->assertEquals(['isWriteable'], get_class_methods($this->Checkup->Backups));
        $this->assertEquals([getConfig('DatabaseBackup.target') => true], $this->Checkup->Backups->isWriteable());
    }

    /**
     * Test for `$KCFinder` property and methods from `KCFinder` class
     * @test
     */
    public function testKCFinder()
    {
        create_kcfinder_files();
        $this->assertInstanceof(KCFinder::class, $this->Checkup->KCFinder);
        $this->assertEquals(['htaccess', 'isAvailable', 'version'], get_class_methods($this->Checkup->KCFinder));
        $this->assertTrue($this->Checkup->KCFinder->htaccess());
        $this->assertTrue($this->Checkup->KCFinder->isAvailable());
        $this->assertRegExp('/[\d\.]+/', $this->Checkup->KCFinder->version());

        //If the `isAvailable()` method returns `false`, the `version()` method
        //  will also return `false`
        $KCFinder = $this->getMockBuilder(KCFinder::class)
            ->setMethods(['isAvailable'])
            ->getMock();
        $KCFinder->method('isAvailable')->will($this->returnValue(false));
        $this->assertNull($KCFinder->version());
    }

    /**
     * Test for `$PHP` property and methods from `PHP` class
     * @test
     */
    public function testPHP()
    {
        $this->assertInstanceof(PHP::class, $this->Checkup->PHP);
        $this->assertEquals(['extensions', 'getVersion'], get_class_methods($this->Checkup->PHP));
        $this->assertNotEmpty($this->Checkup->PHP->extensions());
        $this->assertRegExp('/^7\.\d+\.\d+$/', $this->Checkup->PHP->getVersion());
    }

    /**
     * Test for `$Plugin` property and methods from `Plugin` class
     * @test
     */
    public function testPlugin()
    {
        $this->assertInstanceof(Plugin::class, $this->Checkup->Plugin);
        $this->assertEquals(['versions'], get_class_methods($this->Checkup->Plugin));
        $this->assertArrayKeysEqual(['me_cms', 'others'], $this->Checkup->Plugin->versions());
        $this->assertNotEmpty($this->Checkup->Plugin->versions()['me_cms']);
        $this->assertNotEmpty($this->Checkup->Plugin->versions()['others']);
    }

    /**
     * Test for `$TMP` property and methods from `TMP` class
     * @test
     */
    public function testTMP()
    {
        $this->assertInstanceof(TMP::class, $this->Checkup->TMP);
        $this->assertEquals(['isWriteable'], get_class_methods($this->Checkup->TMP));

        $result = $this->Checkup->TMP->isWriteable();
        $this->assertNotEmpty($result);
        foreach ($result as $path => $isWriteable) {
            $this->assertStringStartsWith(TMP, $path);
            $this->assertTrue($isWriteable);
        }
    }

    /**
     * Test for `$Webroot` property and methods from `Webroot` class
     * @test
     */
    public function testWebroot()
    {
        $this->assertInstanceof(Webroot::class, $this->Checkup->Webroot);
        $this->assertEquals(['isWriteable'], get_class_methods($this->Checkup->Webroot));

        $result = $this->Checkup->Webroot->isWriteable();
        $this->assertNotEmpty($result);
        foreach ($result as $path => $isWriteable) {
            $this->assertStringStartsWith(WWW_ROOT, $path);
            $this->assertTrue($isWriteable);
        }
    }
}
