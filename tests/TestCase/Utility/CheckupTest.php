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
namespace MeCms\Test\TestCase\Utility;

use MeCms\Utility\Checkup;
use MeTools\TestSuite\TestCase;

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
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Checkup = new Checkup;
    }

    /**
     * Test for `$Apache` property and methods from
     *  `\MeCms\Utility\Checkups\Apache` class
     * @test
     */
    public function testApache()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\Apache', $this->Checkup->Apache);
        $this->assertEquals(['modules', 'version'], get_class_methods($this->Checkup->Apache));
        $this->assertArrayKeysEqual(['expires', 'rewrite'], $this->Checkup->Apache->modules());
        $this->assertRegExp('/[\d\.]+/', $this->Checkup->Apache->version());
    }

    /**
     * Test for `$Backups` property and methods from
     *  `\MeCms\Utility\Checkups\Backups` class
     * @test
     */
    public function testBackups()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\Backups', $this->Checkup->Backups);
        $this->assertEquals(['isWriteable'], get_class_methods($this->Checkup->Backups));
        $this->assertEquals([getConfig(DATABASE_BACKUP . '.target') => true], $this->Checkup->Backups->isWriteable());
    }

    /**
     * Test for `$KCFinder` property and methods from
     *  `\MeCms\Utility\Checkups\KCFinder` class
     * @test
     */
    public function testKCFinder()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\KCFinder', $this->Checkup->KCFinder);
        $this->assertEquals(['htaccess', 'version'], get_class_methods($this->Checkup->KCFinder));
        $this->assertTrue($this->Checkup->KCFinder->htaccess());
        $this->assertRegExp('/[\d\.]+/', $this->Checkup->KCFinder->version());
    }

    /**
     * Test for `$PHP` property and methods from
     *  `\MeCms\Utility\Checkups\PHP` class
     * @test
     */
    public function testPHP()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\PHP', $this->Checkup->PHP);
        $this->assertEquals(['extensions'], get_class_methods($this->Checkup->PHP));
    }

    /**
     * Test for `$Plugin` property and methods from
     *  `\MeCms\Utility\Checkups\Plugin` class
     * @test
     */
    public function testPlugin()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\Plugin', $this->Checkup->Plugin);
        $this->assertEquals(['versions'], get_class_methods($this->Checkup->Plugin));
        $this->assertArrayKeysEqual(['me_cms', 'others'], $this->Checkup->Plugin->versions());
        $this->assertNotEmpty($this->Checkup->Plugin->versions()['me_cms']);
        $this->assertNotEmpty($this->Checkup->Plugin->versions()['others']);
    }

    /**
     * Test for `$TMP` property and methods from
     *  `\MeCms\Utility\Checkups\TMP` class
     * @test
     */
    public function testTMP()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\TMP', $this->Checkup->TMP);
        $this->assertEquals(['__construct', 'isWriteable'], get_class_methods($this->Checkup->TMP));

        $result = $this->Checkup->TMP->isWriteable();
        $this->assertNotEmpty($result);

        foreach($result as $path => $isWriteable) {
            $this->assertStringStartsWith(TMP, $path);
            $this->assertTrue($isWriteable);
        }
    }

    /**
     * Test for `$Webroot` property and methods from
     *  `\MeCms\Utility\Checkups\Webroot` class
     * @test
     */
    public function testWebroot()
    {
        $this->assertInstanceof('MeCms\Utility\Checkups\Webroot', $this->Checkup->Webroot);
        $this->assertEquals(['__construct', 'isWriteable'], get_class_methods($this->Checkup->Webroot));

        $result = $this->Checkup->Webroot->isWriteable();
        $this->assertNotEmpty($result);

        foreach($result as $path => $isWriteable) {
            $this->assertStringStartsWith(WWW_ROOT, $path);
            $this->assertTrue($isWriteable);
        }
    }
}
