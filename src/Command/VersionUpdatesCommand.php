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
 * @since       2.26.1
 */

namespace MeCms\Command;

use Cake\Cache\Cache;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Driver\Postgres;
use MeTools\Command\Command;
use Tools\Filesystem;

/**
 * Performs some updates to the database or files needed for versioning
 */
class VersionUpdatesCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Performs some updates to the database or files needed for versioning'));
    }

    /**
     * Adds the `last_logins` field to `Users` table
     * @return void
     * @since 2.30.7-RC4
     */
    public function addLastLoginsField(): void
    {
        Cache::clear('_cake_model_');

        $this->getTableLocator()->clear();
        $Users = $this->getTableLocator()->get('MeCms.Users');
        if (!$Users->getSchema()->hasColumn('last_logins')) {
            $connection = $Users->getConnection();
            $command = 'ALTER TABLE `' . $Users->getTable() . '` ADD `last_logins` TEXT NULL DEFAULT NULL AFTER `last_name`;';
            if ($connection->getDriver() instanceof Postgres) {
                $command = 'ALTER TABLE ' . $Users->getTable() . ' ADD COLUMN last_logins TEXT NULL DEFAULT NULL';
            }
            $connection->execute($command);
        }
    }

    /**
     * Adds the `enable_comments` field to `Pages` and `Posts` tables
     * @return void
     * @since 2.26.3
     */
    public function addEnableCommentsField(): void
    {
        Cache::clear('_cake_model_');

        $this->getTableLocator()->clear();

        foreach (['Pages', 'Posts'] as $tableName) {
            $Table = $this->getTableLocator()->get('MeCms.' . $tableName);
            if (!$Table->getSchema()->hasColumn('enable_comments')) {
                $connection = $Table->getConnection();
                $command = 'ALTER TABLE `' . $Table->getTable() . '` ADD `enable_comments` BOOLEAN NOT NULL DEFAULT TRUE';
                if ($connection->getDriver() instanceof Postgres) {
                    $command = 'ALTER TABLE ' . $Table->getTable() . ' ADD COLUMN enable_comments BOOLEAN NOT NULL DEFAULT TRUE';
                }
                $connection->execute($command);
            }
        }
    }

    /**
     * Alter the length of the `tag` column of the `tags` table
     * @return void
     */
    public function alterTagColumnSize(): void
    {
        $this->getTableLocator()->clear();
        $Tags = $this->getTableLocator()->get('MeCms.Tags');
        if ($Tags->getSchema()->getColumn('tag')['length'] < 255) {
            $connection = $Tags->getConnection();
            $command = 'ALTER TABLE ' . $Tags->getTable() . ' MODIFY tag varchar(255) NOT NULL';
            if ($connection->getDriver() instanceof Postgres) {
                $command = 'ALTER TABLE ' . $Tags->getTable() . ' ALTER COLUMN tag TYPE varchar(255);';
            }
            $connection->execute($command);
        }
    }

    /**
     * Deletes old directories
     * @return void
     * @since 2.26.2
     */
    public function deleteOldDirectories(): void
    {
        Filesystem::instance()->rmdirRecursive(WWW_ROOT . 'fonts');
        Filesystem::instance()->rmdirRecursive(TMP . 'login');
    }

    /**
     * Performs some updates to the database or files needed for versioning
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->addEnableCommentsField();
        $this->addLastLoginsField();
        $this->alterTagColumnSize();
        $this->deleteOldDirectories();

        return null;
    }
}
