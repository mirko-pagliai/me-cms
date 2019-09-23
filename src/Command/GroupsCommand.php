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
namespace MeCms\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use MeCms\Model\Entity\UsersGroup;
use MeTools\Console\Command;

/**
 * Lists user groups
 */
class GroupsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Lists user groups'));
    }

    /**
     * Lists user groups
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->loadModel('MeCms.UsersGroups');

        $table = $this->UsersGroups->find()->map(function (UsersGroup $group) {
            return [(string)$group->id, $group->name, $group->label, (string)$group->user_count];
        })->toList();

        //Checks for user groups
        if (!$table) {
            return $io->error(__d('me_cms', 'There are no user groups'));
        }

        array_unshift($table, [I18N_ID, I18N_NAME, I18N_LABEL, I18N_USERS]);
        $io->helper('table')->output($table);

        return null;
    }
}
