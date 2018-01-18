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
namespace MeCms\Shell;

use Cake\Console\ConsoleIo;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use MeTools\Core\Plugin;
use MeTools\Shell\InstallShell as BaseInstallShell;

/**
 * Executes some tasks to make the system ready to work
 */
class InstallShell extends BaseInstallShell
{
    /**
     * Configuration files to be copied
     * @var array
     */
    protected $config = [];

    /**
     * Construct
     * @param \Cake\Console\ConsoleIo|null $io An io instance
     * @uses $config
     * @uses $links
     * @uses $paths
     * @uses $questions
     * @uses MeTools\Shell\InstallShell::__construct()
     */
    public function __construct(ConsoleIo $io = null)
    {
        parent::__construct($io);

        //Configuration files to be copied
        $this->config = [
            ME_CMS . '.recaptcha',
            ME_CMS . '.banned_ip',
            ME_CMS . '.me_cms',
            ME_CMS . '.widgets',
        ];

        //Merges assets for which create symbolic links
        $this->links += [
            'js-cookie/js-cookie/src' => 'js-cookie',
            'sunhater/kcfinder' => 'kcfinder',
            'enyo/dropzone/dist' => 'dropzone',
        ];

        //Merges paths to be created and made writable
        $this->paths = array_merge($this->paths, [
            getConfigOrFail(ASSETS . '.target'),
            getConfigOrFail(DATABASE_BACKUP . '.target'),
            getConfigOrFail(THUMBER . '.target'),
            BANNERS,
            LOGIN_RECORDS,
            PHOTOS,
            UPLOADED,
            USER_PICTURES,
            TMP . 'login',
        ]);

        //Questions used by `all()` method
        $this->questions = array_merge($this->questions, [
            [
                'question' => __d('me_tools', 'Copy configuration files?'),
                'default' => 'Y',
                'method' => 'copyConfig',
            ],
            [
                'question' => __d('me_tools', 'Fix {0}?', 'KCFinder'),
                'default' => 'Y',
                'method' => 'fixKcfinder',
            ],
            [
                'question' => __d('me_cms', 'Run the installer of the other plugins?'),
                'default' => 'Y',
                'method' => 'runFromOtherPlugins',
            ],
            [
                'question' => __d('me_cms', 'Create the user groups?'),
                'default' => 'N',
                'method' => 'createGroups',
            ],
            [
                'question' => __d('me_cms', 'Create an admin user?'),
                'default' => 'N',
                'method' => 'createAdmin',
            ],
        ]);
    }

    /**
     * Gets others plugins that have the `InstallShell` class
     * @return array
     */
    protected function getOtherPlugins()
    {
        return collection(Plugin::all(['exclude' => [ME_TOOLS, ME_CMS], 'order' => false]))
            ->filter(function ($plugin) {
                $class = App::classname($plugin . '.InstallShell', 'Shell');

                return $class && method_exists($class, 'all');
            })
            ->toList();
    }

    /**
     * Copies the configuration files
     * @return void
     * @uses $config
     * @uses MeTools\Console\Shell::copyFile()
     * @uses MeTools\Core\Plugin::path()
     */
    public function copyConfig()
    {
        foreach ($this->config as $file) {
            list($plugin, $file) = pluginSplit($file);

            $this->copyFile(
                Plugin::path($plugin, 'config' . DS . $file . '.php'),
                CONFIG . $file . '.php'
            );
        }
    }

    /**
     * Creates and admin user
     * @return int Cli command exit code. 0 is success
     * @see MeCms\Shell\User::add()
     */
    public function createAdmin()
    {
        return $this->dispatchShell(ME_CMS . '.user', 'add', '--group', 1);
    }

    /**
     * Creates default user groups
     * @return bool
     */
    public function createGroups()
    {
        $this->loadModel(ME_CMS . '.UsersGroups');

        if (!$this->UsersGroups->find()->isEmpty()) {
            $this->err(__d('me_cms', 'Some user groups already exist'));

            return false;
        }

        //Truncates the table. This resets IDs
        ConnectionManager::get('default')->execute(sprintf('TRUNCATE TABLE `%s`', $this->UsersGroups->getTable()));

        $entities = $this->UsersGroups->newEntities([
            ['id' => 1, 'name' => 'admin', 'label' => 'Admin'],
            ['id' => 2, 'name' => 'manager', 'label' => 'Manager'],
            ['id' => 3, 'name' => 'user', 'label' => 'User'],
        ]);

        $this->UsersGroups->saveMany($entities);
        $this->verbose(__d('me_cms', 'The user groups have been created'));

        return true;
    }

    /**
     * Fixes KCFinder.
     * Creates the file `vendor/kcfinder/.htaccess`
     * @return bool `false` on failure
     * @see http://kcfinder.sunhater.com/integrate
     * @uses MeTools\Console\Shell::createFile()
     */
    public function fixKcfinder()
    {
        //Checks for KCFinder
        if (!is_readable(KCFINDER . 'browse.php')) {
            $this->err(__d('me_tools', '{0} is not available', 'KCFinder'));

            return false;
        }

        $this->createFile(
            WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . '.htaccess',
            'php_value session.cache_limiter must-revalidate' . PHP_EOL .
            'php_value session.cookie_httponly On' . PHP_EOL .
            'php_value session.cookie_lifetime 14400' . PHP_EOL .
            'php_value session.gc_maxlifetime 14400' . PHP_EOL .
            'php_value session.name CAKEPHP'
        );
    }

    /**
     * Gets the option parser instance and configures it.
     * @return ConsoleOptionParser
     * @uses MeTools\Shell\InstallShell::getOptionParser()
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('copyConfig', ['help' => __d('me_cms', 'Copies the configuration files')]);
        $parser->addSubcommand('createAdmin', ['help' => __d('me_cms', 'Creates an admin user')]);
        $parser->addSubcommand('createGroups', ['help' => __d('me_cms', 'Creates the user groups')]);
        $parser->addSubcommand('fixKcfinder', ['help' => __d('me_cms', 'Fixes {0}', 'KCFinder')]);
        $parser->addSubcommand('runFromOtherPlugins', ['help' => __d('me_cms', 'Runs the installer from other plugins')]);

        return $parser;
    }

    /**
     * Runs the `InstallShell::all()` method from other plugins
     * @return array Array of the cli command exit code. 0 is success
     * @uses getOtherPlugins()
     */
    public function runFromOtherPlugins()
    {
        $executed = [];

        foreach ($this->getOtherPlugins() as $plugin) {
            $executed[$plugin] = $this->dispatchShell([
                'command' => [sprintf('%s.install', $plugin), 'all'],
                'extra' => $this->params,
            ]);
        }

        return $executed;
    }
}
