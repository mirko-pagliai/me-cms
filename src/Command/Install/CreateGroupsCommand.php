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
 * @since       2.26.0
 */
namespace MeCms\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use MeTools\Console\Command;

/**
 * Creates the user groups
 */
class CreateGroupsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        return $parser->setDescription(__d('me_cms', 'Creates the user groups'));
    }

    /**
     * Creates the user groups
     * @param Arguments $args The command arguments
     * @param ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadModel('MeCms.UsersGroups');

        if (!$this->UsersGroups->find()->isEmpty()) {
            $io->error(__d('me_cms', 'Some user groups already exist'));

            return null;
        }

        //Truncates the table (this resets IDs), then saves groups
        ConnectionManager::get('default')->execute(sprintf('TRUNCATE TABLE `%s`', $this->UsersGroups->getTable()));
        $this->UsersGroups->saveMany($this->UsersGroups->newEntities([
            ['id' => 1, 'name' => 'admin', 'label' => 'Admin'],
            ['id' => 2, 'name' => 'manager', 'label' => 'Manager'],
            ['id' => 3, 'name' => 'user', 'label' => 'User'],
        ]));
        $io->verbose(__d('me_cms', 'The user groups have been created'));

        return null;
    }
}
