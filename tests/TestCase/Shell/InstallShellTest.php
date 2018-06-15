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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\Core\Plugin;
use MeCms\Shell\InstallShell;
use MeTools\TestSuite\ConsoleIntegrationTestCase;

/**
 * InstallShellTest class
 */
class InstallShellTest extends ConsoleIntegrationTestCase
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
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $out;

    /**
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $err;

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

        $this->InstallShell = new InstallShell;
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
        foreach (['config', 'links', 'paths'] as $property) {
            $this->assertNotEmpty($this->getProperty($this->InstallShell, $property));
        }
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

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        //Gets all methods from `InstallShell`, except for the `all()` method
        $methods = array_diff(array_merge(
            get_child_methods(ME_TOOLS . '\Shell\InstallShell'),
            get_child_methods(InstallShell::class)
        ), ['all']);

        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(array_merge(['in', '_stop'], $methods))
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
        array_unshift($expectedMethodsCalledInOrder, 'called `createDirectories`');
        array_push($expectedMethodsCalledInOrder, 'called `createGroups`', 'called `createAdmin`');
        $this->InstallShell->all();

        $this->assertEquals($expectedMethodsCalledInOrder, $this->out->messages());
        $this->assertEmpty($this->err->messages());
    }

    /**
     * Tests for `copyConfig()` method
     * @test
     */
    public function testCopyConfig()
    {
        $this->exec('me_cms.install copy_config -v');
        $this->assertExitWithSuccess();

        foreach ($this->getProperty($this->InstallShell, 'config') as $file) {
            $file = rtr(CONFIG . pluginSplit($file)[1] . '.php');
            $this->assertOutputContains('File or directory `' . $file . '` already exists');
        }
    }

    /**
     * Test for `createAdmin()` method
     * @test
     */
    public function testCreateAdmin()
    {
        $this->InstallShell = $this->getMockBuilder(InstallShell::class)
            ->setMethods(['in', '_stop', 'dispatchShell'])
            ->getMock();

        $this->InstallShell->expects($this->once())
            ->method('dispatchShell')
            ->with(ME_CMS . '.user', 'add', '--group', 1);

        $this->InstallShell->createAdmin();
    }

    /**
     * Test for `createGroups()` method
     * @test
     */
    public function testCreateGroups()
    {
        //A group already exists
        $this->exec('me_cms.install create_groups -v');
        $this->assertExitWithError();
        $this->assertErrorContains('<error>Some user groups already exist</error>');

        $groups = TableRegistry::get(ME_CMS . '.UsersGroups');

        //Deletes all groups
        $this->assertNotEquals(0, $groups->deleteAll(['id >=' => '1']));

        $this->exec('me_cms.install create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('The user groups have been created');
    }

    /**
     * Test for `fixKcfinder()` method
     * @test
     */
    public function testFixKcfinder()
    {
        safe_unlink(KCFINDER . '.htaccess');

        $this->exec('me_cms.install fix_kcfinder -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('Creating file ' . KCFINDER . '.htaccess');
        $this->assertOutputContains('<success>Wrote</success> `' . KCFINDER . '.htaccess' . '`');

        $this->assertStringEqualsFile(
            KCFINDER . '.htaccess',
            'php_value session.cache_limiter must-revalidate' . PHP_EOL .
            'php_value session.cookie_httponly On' . PHP_EOL .
            'php_value session.cookie_lifetime 14400' . PHP_EOL .
            'php_value session.gc_maxlifetime 14400' . PHP_EOL .
            'php_value session.name CAKEPHP'
        );

        $browseFile = KCFINDER . 'browse.php';
        $browseFileContent = file_get_contents($browseFile);
        safe_unlink($browseFile);

        //For now KCFinder is not available
        $this->exec('me_cms.install fix_kcfinder -v');
        $this->assertExitWithError();
        $this->assertErrorContains('<error>KCFinder is not available</error>');

        file_put_contents($browseFile, $browseFileContent);
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
            'copy_config',
            'copy_fonts',
            'create_admin',
            'create_directories',
            'create_groups',
            'create_plugins_links',
            'create_robots',
            'create_vendors_links',
            'fix_composer_json',
            'fix_kcfinder',
            'run_from_other_plugins',
            'set_permissions',
        ], $parser->subcommands());
        $this->assertEquals('Executes some tasks to make the system ready to work', $parser->getDescription());
        $this->assertArrayKeysEqual(['force', 'help', 'quiet', 'verbose'], $parser->options());
    }
}
