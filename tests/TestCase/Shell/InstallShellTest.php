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
namespace MeCms\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\Core\Plugin;
use MeCms\Shell\InstallShell;
use MeTools\TestSuite\TestCase;

/**
 * InstallShellTest class
 */
class InstallShellTest extends TestCase
{
    /**
     * @var \MeCms\Shell\InstallShell
     */
    protected $InstallShell;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = ['plugin.me_cms.users_groups'];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->out = new ConsoleOutput;
        $this->err = new ConsoleOutput;
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
     * Test for `getOtherPlugins()` method
     * @test
     */
    public function testGetOtherPlugins()
    {
        $this->assertEmpty($this->invokeMethod($this->InstallShell, 'getOtherPlugins'));

        Plugin::load('TestPlugin');

        $this->assertEquals(['TestPlugin'], $this->invokeMethod($this->InstallShell, 'getOtherPlugins'));
    }

    public function testAll()
    {
        //Gets all methods from `InstallShell`
        $methods = array_diff(array_merge(
            getChildMethods(METOOLS . '\Shell\InstallShell'),
            getChildMethods(InstallShell::class)
        ), ['all']);

        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(array_merge(['in', '_stop'], $methods))
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->InstallShell->method('in')->will($this->returnValue('y'));

        //Sets a callback for each method
        foreach ($methods as $method) {
            $this->InstallShell->method($method)
                ->will($this->returnCallback(function () use ($method) {
                    $this->out->write(sprintf('called `%s`', $method));
                }));
        }

        Plugin::load('TestPlugin');

        //Calls with `force` options
        $this->InstallShell->params['force'] = true;
        $this->InstallShell->all();

        $expectedMethodsCalledInOrder = [
            'called `createDirectories`',
            'called `setPermissions`',
            'called `createRobots`',
            'called `fixComposerJson`',
            'called `createPluginsLinks`',
            'called `createVendorsLinks`',
            'called `copyFonts`',
            'called `copyConfig`',
            'called `fixKcfinder`',
            'called `runFromOtherPlugins`',
        ];

        $this->assertEquals($expectedMethodsCalledInOrder, $this->out->messages());

        //Resets out messages()
        $this->setProperty($this->out, '_out', []);

        //Calls with no interactive mode
        unset($this->InstallShell->params['force']);
        $this->InstallShell->all();

        $this->assertEquals(array_merge($expectedMethodsCalledInOrder, [
            'called `createGroups`',
            'called `createAdmin`',
        ]), $this->out->messages());
        $this->assertEmpty($this->err->messages());
    }

    /**
     * Tests for `copyConfig()` method
     * @test
     */
    public function testCopyConfig()
    {
        $this->InstallShell->copyConfig();

        $this->assertEquals([
            'File or directory tests/test_app/TestApp/config/recaptcha.php already exists',
            'File or directory tests/test_app/TestApp/config/banned_ip.php already exists',
            'File or directory tests/test_app/TestApp/config/me_cms.php already exists',
            'File or directory tests/test_app/TestApp/config/widgets.php already exists',
        ], $this->out->messages());

        $this->assertEmpty($this->err->messages());
    }

    /**
     * Test for `createAdmin()` method
     * @test
     */
    public function testCreateAdmin()
    {
        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(['in', '_stop', 'dispatchShell'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->InstallShell->method('dispatchShell')
            ->will($this->returnCallback(function () {
                return ['method' => 'dispatchShell', 'args' => func_get_args()];
            }));

        $this->assertEquals([
            'method' => 'dispatchShell',
            'args' => [ME_CMS . '.user', 'add', '--group', 1],
        ], $this->InstallShell->createAdmin());
    }

    /**
     * Test for `createGroups()` method
     * @test
     */
    public function testCreateGroups()
    {
        //A group already exists
        $this->assertFalse($this->InstallShell->createGroups());

        $groups = TableRegistry::get(ME_CMS . '.UsersGroups');

        //Deletes all groups
        $this->assertNotEquals(0, $groups->deleteAll(['id >=' => '1']));

        $this->assertEmpty($groups->find()->toArray());
        $this->assertTrue($this->InstallShell->createGroups());
        $this->assertNotEmpty($groups->find()->toArray());
        $this->assertEquals(3, count($groups->find()->toArray()));

        $this->assertEquals(['The user groups have been created'], $this->out->messages());
        $this->assertEquals(['<error>Some user groups already exist</error>'], $this->err->messages());
    }

    /**
     * Test for `fixKcfinder()` method
     * @test
     */
    public function testFixKcfinder()
    {
        $file = WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . '.htaccess';

        //@codingStandardsIgnoreStart
        @unlink($file);
        @unlink(dirname($file) . DS . 'index.php');
        @rmdir(dirname($file));
        //@codingStandardsIgnoreEnd

        //For now KCFinder is not available
        $this->InstallShell->fixKcfinder();

        //@codingStandardsIgnoreLine
        @mkdir(dirname($file), 0777, true);
        file_put_contents(dirname($file) . DS . 'index.php', null);

        $this->InstallShell->fixKcfinder();
        $this->assertFileExists($file);

        $this->assertEquals(
            'php_value session.cache_limiter must-revalidate' . PHP_EOL .
            'php_value session.cookie_httponly On' . PHP_EOL .
            'php_value session.cookie_lifetime 14400' . PHP_EOL .
            'php_value session.gc_maxlifetime 14400' . PHP_EOL .
            'php_value session.name CAKEPHP',
            file_get_contents($file)
        );

        $this->assertNotEmpty($this->out->messages());
        $this->assertEquals(['<error>KCFinder is not available</error>'], $this->err->messages());
    }

    /**
     * Test for `runFromOtherPlugins()` method
     * @test
     */
    public function testRunFromOtherPlugins()
    {
        $this->assertEmpty($this->InstallShell->runFromOtherPlugins());

        Plugin::load('TestPlugin');

        $this->assertEquals(['TestPlugin' => 0], $this->InstallShell->runFromOtherPlugins());
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->InstallShell->getOptionParser();

        $this->assertInstanceOf('Cake\Console\ConsoleOptionParser', $parser);
        $this->assertArrayKeysEqual([
            'all',
            'copyConfig',
            'copyFonts',
            'createAdmin',
            'createDirectories',
            'createGroups',
            'createPluginsLinks',
            'createRobots',
            'createVendorsLinks',
            'fixComposerJson',
            'fixKcfinder',
            'runFromOtherPlugins',
            'setPermissions',
        ], $parser->subcommands());
        $this->assertEquals('Executes some tasks to make the system ready to work', $parser->getDescription());
        $this->assertEquals(['force', 'help', 'quiet', 'verbose'], array_keys($parser->options()));
    }
}
