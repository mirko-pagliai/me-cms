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
        $this->links = array_merge($this->links, [
            'js-cookie/js-cookie/src' => 'js-cookie',
            'sunhater/kcfinder' => 'kcfinder',
            'enyo/dropzone/dist' => 'dropzone',
        ]);

        //Merges paths to be created and made writable
        $this->paths = array_merge($this->paths, [
            getConfigOrFail(ASSETS . '.target'),
            getConfigOrFail(DATABASE_BACKUP . '.target'),
            getConfigOrFail(THUMBER . '.target'),
            BANNERS,
            LOGIN_RECORDS,
            PHOTOS,
            UPLOADED,
            TMP . 'login',
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
     * Executes all available tasks
     * @return void
     * @uses MeTools\Shell\InstallShell::all()
     * @uses getOtherPlugins()
     * @uses copyConfig()
     * @uses createAdmin()
     * @uses createGroups()
     * @uses fixKcfinder()
     * @uses runFromOtherPlugins()
     */
    public function all()
    {
        parent::all();

        if ($this->param('force')) {
            $this->copyConfig();
            $this->fixKcfinder();
            $this->runFromOtherPlugins();

            return;
        }

        $ask = $this->in(__d('me_cms', 'Copy configuration files?'), ['Y', 'n'], 'Y');
        if (in_array($ask, ['Y', 'y'])) {
            $this->copyConfig();
        }

        $ask = $this->in(__d('me_tools', 'Fix {0}?', 'KCFinder'), ['Y', 'n'], 'Y');
        if (in_array($ask, ['Y', 'y'])) {
            $this->fixKcfinder();
        }

        if ($this->getOtherPlugins()) {
            $ask = $this->in(__d('me_cms', 'Run the installer of the other plugins?'), ['Y', 'n'], 'Y');
            if (in_array($ask, ['Y', 'y'])) {
                $this->runFromOtherPlugins();
            }
        }

        $ask = $this->in(__d('me_cms', 'Create the user groups?'), ['y', 'N'], 'N');
        if (in_array($ask, ['Y', 'y'])) {
            $this->createGroups();
        }

        $ask = $this->in(__d('me_cms', 'Create an admin user?'), ['y', 'N'], 'N');
        if (in_array($ask, ['Y', 'y'])) {
            $this->createAdmin();
        }
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
        if (!is_readable(WWW_ROOT . 'vendor' . DS . 'kcfinder')) {
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
