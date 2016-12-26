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
namespace MeCms\Console;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use MeTools\Console\Shell;

/**
 * Provided some provides some useful methods for the `UpdateShell` classes
 */
class BaseUpdateShell extends Shell
{
    /**
     * Database connection
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * Now for MySql
     * @var \Cake\I18n\Time
     */
    protected $now;

    /**
     * Construct
     * @param \Cake\Console\ConsoleIo|null $io An io instance
     * @return void
     * @uses $connection
     * @uses $now
     */
    public function __construct(\Cake\Console\ConsoleIo $io = null)
    {
        parent::__construct($io);

        //Gets database connection
        $this->connection = ConnectionManager::get('default');

        //Sets now for MySql
        $this->now = new Time;
    }

    /**
     * Checks if a column exists
     * @param string $column Column name
     * @param string $table Table name
     * @return bool
     * @uses _getColumns()
     */
    protected function _checkColumn($column, $table)
    {
        return in_array($column, $this->_getColumns($table));
    }

    /**
     * Gets all update methods.
     *
     * Each value contains the name method and the version number.
     * @return array
     */
    protected function _getAllUpdateMethods()
    {
        $methods = getChildMethods(get_called_class());

        return af(array_map(function ($method) {
            //Returns array with the name method and the version number
            if (preg_match('/^to([0-9]+)v([0-9]+)v(.+)$/', $method, $matches)) {
                return [
                    'name' => $method,
                    'version' => $matches[1] . '.' . $matches[2] . '.' . $matches[3],
                ];
            }

            return false;
        }, $methods));
    }

    /**
     * Gets the table columns
     * @param string $table Table name
     * @return array
     * @uses $connection
     */
    protected function _getColumns($table)
    {
        $columns = $this->connection->execute(sprintf('SHOW COLUMNS FROM %s;', $table))->fetchAll();

        return array_map(function ($column) {
            return firstValue($column);
        }, $columns);
    }

    /**
     * Gets the latest update method.
     *
     * Return an array with the name method and the version number.
     * @return array
     * @uses _getAllUpdateMethods()
     */
    protected function _getLatestUpdateMethod()
    {
        return firstValue($this->_getAllUpdateMethods());
    }

    /**
     * Checks if a table exists
     * @param string $table Table name
     * @return bool
     * @uses _tables()
     */
    protected function _tableExists($table)
    {
        return in_array($table, $this->_tables());
    }

    /**
     * Gets the tables list
     * @return array
     * @uses $connection
     */
    protected function _tables()
    {
        $tables = $this->connection->execute(sprintf('SHOW TABLES;'))->fetchAll();

        return array_map(function ($table) {
            return firstValue($table);
        }, $tables);
    }

    /**
     * Performs all available updates
     * @return void
     * @uses _getAllUpdateMethods()
     */
    public function all()
    {
        $methods = array_reverse($this->_getAllUpdateMethods());

        foreach ($methods as $method) {
            $this->verbose(__d('me_cms', 'Upgrading to {0}', $method['version']));

            //Calls dynamically each method
            $this->{$method['name']}();
        }
    }

    /**
     * Performs the latest update available
     * @return void
     * @uses _getLatestUpdateMethod()
     */
    public function latest()
    {
        list($name, $version) = array_values($this->_getLatestUpdateMethod());

        $this->verbose(__d('me_cms', 'Upgrading to {0}', $version));

        //Calls dynamically the method
        $this->{$name}();
    }

    /**
     * Gets the option parser instance and configures it.
     * @return ConsoleOptionParser
     * @uses _getAllUpdateMethods()
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('all', ['help' => __d('me_cms', 'Performs all available updates')]);
        $parser->addSubcommand('latest', ['help' => __d('me_cms', 'Performs the latest update available')]);

        $methods = $this->_getAllUpdateMethods();

        //Adds all update methods to the parser
        foreach ($methods as $method) {
            $parser->addSubcommand($method['name'], [
                'help' => __d('me_cms', 'Updates to {0} version', $method['version']),
            ]);
        }

        return $parser;
    }
}
