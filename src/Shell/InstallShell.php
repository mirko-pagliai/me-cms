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
namespace MeCms\Shell;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use MeTools\Core\Plugin;
use MeTools\Shell\InstallShell as BaseInstallShell;

/**
 * Executes some tasks to make the system ready to work
 */
class InstallShell extends BaseInstallShell
{
    /**
     * Construct
     * @param \Cake\Console\ConsoleIo|null $io An io instance
     * @uses $config
     * @uses $links
     * @uses $paths
     * @uses MeTools\Shell\InstallShell::__construct()
     */
    public function __construct(\Cake\Console\ConsoleIo $io = null)
    {
        parent::__construct($io);

        //Configuration files to be copied
        $this->config = am($this->config, [
            'MeCms.banned_ip',
            'MeCms.me_cms',
            'MeCms.widgets',
        ]);

        //Merges assets for which create symbolic links
        $this->links = am($this->links, [
            'js-cookie/js-cookie/src' => 'js-cookie',
            'sunhater/kcfinder' => 'kcfinder',
            'enyo/dropzone/dist' => 'dropzone',
        ]);

        //Merges paths to be created and made writable
        $this->paths = am($this->paths, [
            Configure::read('Assets.target'),
            Configure::read('MysqlBackup.target'),
            BANNERS,
            PHOTOS,
            Configure::read('Thumbs.target'),
            TMP . 'login',
        ]);
    }

    /**
     * Gets others plugins that have the `InstallShell` class
     * @return array
     */
    protected function _getOtherPlugins()
    {
        //Gets all plugins
        $plugins = Plugin::all(['exclude' => [METOOLS, MECMS], 'order' => false]);

        //Gets only the plugins that have the `InstallShell` class
        $plugins = array_map(function ($plugin) {
            $class = '\\' . $plugin . '\Shell\InstallShell';

            if (class_exists($class) && method_exists($class, 'all')) {
                return $plugin;
            }

            return false;
        }, $plugins);

        return array_values(af($plugins));
    }

    /**
     * Runs  the `InstallShell` class from other plugins
     * @return array. Array of the cli command exit code. 0 is success
     * @uses _getOtherPlugins()
     */
    protected function _runOtherPlugins()
    {
        $executed = [];

        foreach ($this->_getOtherPlugins() as $plugin) {
            $executed[$plugin] = $this->dispatchShell([
                'command' => [sprintf('%s.install', $plugin), 'all'],
                'extra' => $this->params,
            ]);
        }

        return $executed;
    }

    /**
     * Executes all available tasks
     * @return void
     * @uses MeTools\Shell\InstallShell::all()
     * @uses _getOtherPlugins()
     * @uses _runOtherPlugins()
     * @uses createAdmin()
     * @uses createGroups()
     * @uses fixKcfinder()
     */
    public function all()
    {
        parent::all();

        if ($this->param('force')) {
            $this->fixKcfinder();
            $this->_runOtherPlugins();

            return;
        }

        $ask = $this->in(__d('me_tools', 'Fix {0}?', 'KCFinder'), ['Y', 'n'], 'Y');
        if (in_array($ask, ['Y', 'y'])) {
            $this->fixKcfinder();
        }

        $ask = $this->in(__d('me_cms', 'Create the user groups?'), ['y', 'N'], 'N');
        if (in_array($ask, ['Y', 'y'])) {
            $this->createGroups();
        }

        $ask = $this->in(__d('me_cms', 'Create an admin user?'), ['y', 'N'], 'N');
        if (in_array($ask, ['Y', 'y'])) {
            $this->createAdmin();
        }

        if ($this->_getOtherPlugins()) {
            $ask = $this->in(__d('me_cms', 'Run the installer of the other plugins?'), ['Y', 'n'], 'Y');
            if (in_array($ask, ['Y', 'y'])) {
                $this->_runOtherPlugins();
            }
        }
    }

    /**
     * Creates and admin user
     * @return void
     * @see MeCms\Shell\User::add()
     */
    public function createAdmin()
    {
        $this->dispatchShell('MeCms.user', 'add', '--group', 1);
    }

    /**
     * Creates the user groups
     * @return void
     */
    public function createGroups()
    {
        $this->loadModel('MeCms.UsersGroups');

        $groups = $this->UsersGroups->find('all');

        if ($groups->isEmpty()) {
            //Truncates the table. This resets IDs
            ConnectionManager::get('default')->execute(sprintf('TRUNCATE TABLE `%s`', $this->UsersGroups->table()));

            $entities = $this->UsersGroups->newEntities([
                ['id' => 1, 'name' => 'admin', 'label' => 'Admin'],
                ['id' => 2, 'name' => 'manager', 'label' => 'Manager'],
                ['id' => 3, 'name' => 'user', 'label' => 'User'],
            ]);

            if ($this->UsersGroups->saveMany($entities)) {
                $this->verbose(__d('me_cms', 'The user groups have been created'));
            } else {
                $this->err(__d('me_cms', 'The user groups have not been created'));
            }
        }
    }

    /**
     * Fixes KCFinder.
     * Creates the file `vendor/kcfinder/.htaccess`
     * @return void
     * @see http://kcfinder.sunhater.com/integrate
     * @uses MeTools\Console\Shell::createFile()
     */
    public function fixKcfinder()
    {
        //Checks for KCFinder
        if (!is_readable(WWW_ROOT . 'vendor' . DS . 'kcfinder')) {
            $this->err(__d('me_tools', '{0} is not available', 'KCFinder'));

            return;
        }

        $this->createFile(WWW_ROOT . 'vendor' . DS . 'kcfinder' . DS . '.htaccess', '<IfModule mod_php5.c>' . PHP_EOL .
            '   php_value session.cache_limiter must-revalidate' . PHP_EOL .
            '   php_value session.cookie_httponly On' . PHP_EOL .
            '   php_value session.cookie_lifetime 14400' . PHP_EOL .
            '   php_value session.gc_maxlifetime 14400' . PHP_EOL .
            '   php_value session.name CAKEPHP' . PHP_EOL .
            '</IfModule>');
    }

    /**
     * Gets the option parser instance and configures it.
     * @return ConsoleOptionParser
     * @uses MeTools\Shell\InstallShell::getOptionParser()
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('createAdmin', ['help' => __d('me_tools', 'Creates an admin user')]);
        $parser->addSubcommand('createGroups', ['help' => __d('me_tools', 'Creates the user groups')]);
        $parser->addSubcommand('fixKcfinder', ['help' => __d('me_tools', 'Fixes {0}', 'KCFinder')]);

        return $parser;
    }
}
