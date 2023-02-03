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
 * @since       2.26.0
 */

namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use MeTools\Command\Command;

/**
 * Creates the user groups
 * @property \MeCms\Model\Table\UsersGroupsTable $UsersGroups
 */
class CreateGroupsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Creates the user groups'));
    }

    /**
     * Creates the user groups
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     * @throws \Exception
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->getTableLocator()->clear();
        $UsersGroups = $this->getTableLocator()->get('MeCms.UsersGroups');

        if (!$UsersGroups->find()->all()->isEmpty()) {
            return $io->error(__d('me_cms', 'Some user groups already exist'));
        }

        //Truncates the table (this resets IDs), then saves groups
        $command = 'TRUNCATE TABLE `%s`';
        $connection = $UsersGroups->getConnection();
        if ($connection->getDriver() instanceof Sqlite) {
            $command = 'DELETE FROM "sqlite_sequence" WHERE "name"=\'%s\';';
        } elseif ($connection->getDriver() instanceof Postgres) {
            $command = 'truncate %s restart identity';
        }
        $connection->execute(sprintf($command, $UsersGroups->getTable()));

        $UsersGroups->saveMany($UsersGroups->newEntities([
            ['id' => 1, 'name' => 'admin', 'label' => 'Admin'],
            ['id' => 2, 'name' => 'manager', 'label' => 'Manager'],
            ['id' => 3, 'name' => 'user', 'label' => 'User'],
        ]));
        $io->verbose(__d('me_cms', 'The user groups have been created'));

        return null;
    }
}
