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
use MeTools\Console\Command;
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
        return $parser->setDescription(__d(
            'me_cms',
            'Performs some updates to the database or files needed for versioning'
        ));
    }

    /**
     * Adds the `enable_comments` field to `Pages` and `Posts` tables
     * @return void
     * @since 2.26.3
     */
    public function addEnableCommentsField(): void
    {
        Cache::clear('_cake_model_');

        foreach (['Pages', 'Posts'] as $tableName) {
            /** @var \MeCms\Model\Table\AppTable $Table **/
            $Table = $this->loadModel('MeCms.' . $tableName);
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
        /** @var \MeCms\Model\Table\AppTable $Table **/
        $Table = $this->loadModel('MeCms.Tags');
        if ($Table->getSchema()->getColumn('tag')['length'] < 255) {
            $connection = $Table->getConnection();
            $command = 'ALTER TABLE ' . $Table->getTable() . ' MODIFY tag varchar(255) NOT NULL';
            if ($connection->getDriver() instanceof Postgres) {
                $command = 'ALTER TABLE ' . $Table->getTable() . ' ALTER COLUMN tag TYPE varchar(255);';
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
        (new Filesystem())->rmdirRecursive(WWW_ROOT . 'fonts');
    }

    /**
     * Performs some updates to the database or files needed for versioning
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     * @uses addEnableCommentsField()
     * @uses alterTagColumnSize()
     * @uses deleteOldDirectories()
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->addEnableCommentsField();
        $this->alterTagColumnSize();
        $this->deleteOldDirectories();

        return null;
    }
}
