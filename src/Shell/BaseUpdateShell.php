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

use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use MeTools\Console\Shell;

class BaseUpdateShell extends Shell
{
    /**
     * Database connection
     * @see initialize()
     * @var resource
     */
    protected $connection;

    /**
     * Now for MySql
     * @see initialize()
     * @var string
     */
    protected $now;

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
     * Gets the table columns
     * @param string $table Table name
     * @return array
     * @uses $connection
     */
    protected function _getColumns($table)
    {
        $columns = $this->connection->execute(sprintf('SHOW COLUMNS FROM %s;', $table))->fetchAll();

        return array_map(function ($column) {
            return array_values($column)[0];
        }, $columns);
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
            return array_values($table)[0];
        }, $tables);
    }

    /**
     * Gets the option parser instance and configures it.
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        //Scans all methods of the class and adds to the parser all valid
        //  subcommands
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/^to(\d+v\d+v\d+(v(RC|alpha|beta)\d+)?)$/', $method, $matches)) {
                $parser->addSubcommand($method, [
                    'help' => __d('me_cms', 'Updates to {0} version', str_replace('v', '.', $matches[1])),
                ]);
            }
        }

        return $parser;
    }

    /**
     * Initialize
     * @return void
     * @uses $connection
     * @uses $now
     */
    public function initialize()
    {
        parent::initialize();

        //Gets database connection
        $this->connection = ConnectionManager::get('default');

        //Sets now for MySql
        $this->now = new Time();
    }
}
