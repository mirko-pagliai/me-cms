<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;
use MeCms\Core\Plugin;
use MeCms\Shell\InstallShell;
use Reflection\ReflectionTrait;

/**
 * InstallShellTest class
 */
class InstallShellTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Shell\InstallShell
     */
    protected $InstallShell;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->out = new ConsoleOutput();
        $this->err = new ConsoleOutput();
        $this->io = new ConsoleIo($this->out, $this->err);
        $this->io->level(2);

        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(['in', '_stop'])
            ->setConstructorArgs([$this->io])
            ->getMock();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');

        unset($this->InstallShell, $this->io, $this->err, $this->out);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertNotEmpty($this->getProperty($this->InstallShell, 'config'));
        $this->assertNotEmpty($this->getProperty($this->InstallShell, 'links'));
        $this->assertNotEmpty($this->getProperty($this->InstallShell, 'paths'));
    }

    /**
     * Test for `_getOtherPlugins()` method
     * @test
     */
    public function testGetOtherPlugins()
    {
        $this->assertEmpty($this->invokeMethod($this->InstallShell, '_getOtherPlugins'));

        Plugin::load('TestPlugin');

        $this->assertEquals(['TestPlugin'], $this->invokeMethod($this->InstallShell, '_getOtherPlugins'));
    }

    /**
     * Test for `_runOtherPlugins()` method
     * @test
     */
    public function testRunOtherPlugins()
    {
        $this->assertEmpty($this->invokeMethod($this->InstallShell, '_runOtherPlugins'));

        Plugin::load('TestPlugin');

        $this->assertEquals(['TestPlugin' => 0], $this->invokeMethod($this->InstallShell, '_runOtherPlugins'));
    }

    public function testAll()
    {
        $methodsToStub = [
            '_runOtherPlugins',
            'createAdmin',
            'createGroups',
            'fixKcfinder',
            //From MeTools
            'createDirectories',
            'setPermissions',
            'copyConfig',
            'createRobots',
            'fixComposerJson',
            'createVendorsLinks',
            'copyFonts',
        ];

        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(array_merge(['in', '_stop'], $methodsToStub))
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->InstallShell->method('in')
            ->will($this->returnCallback(function () {
                return 'y';
            }));

        //Stubs all methods
        foreach ($methodsToStub as $method) {
            $this->InstallShell->method($method)
                ->will($this->returnCallback(function () use ($method) {
                    $this->out->write(sprintf('called `%s`', $method));
                }));
        }

        Plugin::load('TestPlugin');

        //Calls with `force` options
        $this->InstallShell->params['force'] = true;
        $this->InstallShell->all();

        //Calls with no interactive mode
        unset($this->InstallShell->params['force']);
        $this->InstallShell->all();

        $this->assertEquals([
            'called `createDirectories`',
            'called `setPermissions`',
            'called `copyConfig`',
            'called `createRobots`',
            'called `fixComposerJson`',
            'called `createVendorsLinks`',
            'called `copyFonts`',
            'called `fixKcfinder`',
            'called `_runOtherPlugins`',
            'called `createDirectories`',
            'called `setPermissions`',
            'called `copyConfig`',
            'called `createRobots`',
            'called `fixComposerJson`',
            'called `createVendorsLinks`',
            'called `copyFonts`',
            'called `fixKcfinder`',
            'called `createGroups`',
            'called `createAdmin`',
            'called `_runOtherPlugins`',
        ], $this->out->messages());
        $this->assertEmpty($this->err->messages());
    }

    /**
     * Test for `fixKcfinder()` method
     * @test
     */
    public function testFixKcfinder()
    {
        //For now KCFinder is not available
        $this->InstallShell->fixKcfinder();

        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . '.htaccess';

        //@codingStandardsIgnoreLine
        @mkdir(dirname($file), 0777, true);

        $this->InstallShell->fixKcfinder();
        $this->assertFileExists($file);

        $this->assertEquals('<IfModule mod_php5.c>' . PHP_EOL .
            '   php_value session.cache_limiter must-revalidate' . PHP_EOL .
            '   php_value session.cookie_httponly On' . PHP_EOL .
            '   php_value session.cookie_lifetime 14400' . PHP_EOL .
            '   php_value session.gc_maxlifetime 14400' . PHP_EOL .
            '   php_value session.name CAKEPHP' . PHP_EOL .
            '</IfModule>', file_get_contents($file));

        $this->assertNotEmpty($this->out->messages());
        $this->assertEquals([
            '<error>KCFinder is not available</error>',
        ], $this->err->messages());

        //@codingStandardsIgnoreStart
        @unlink($file);
        @rmdir(dirname($file));
        //@codingStandardsIgnoreEnd
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->InstallShell->getOptionParser();

        $this->assertEquals('Cake\Console\ConsoleOptionParser', get_class($parser));
        $this->assertEquals([
            'all',
            'copyConfig',
            'copyFonts',
            'createAdmin',
            'createDirectories',
            'createGroups',
            'createRobots',
            'createVendorsLinks',
            'fixComposerJson',
            'fixKcfinder',
            'setPermissions',
        ], array_keys($parser->subcommands()));
        $this->assertEquals('Executes some tasks to make the system ready to work', $parser->description());
        $this->assertEquals(['force', 'help', 'quiet', 'verbose'], array_keys($parser->options()));
    }
}
