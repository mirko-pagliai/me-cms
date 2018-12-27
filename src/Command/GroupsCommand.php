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
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        return $parser->setDescription(__d('me_cms', 'Lists user groups'));
    }

    /**
     * Lists user groups
     * @param Arguments $args The command arguments
     * @param ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadModel('MeCms.UsersGroups');

        $groups = $this->UsersGroups->find()->map(function (UsersGroup $group) {
            return $group->extract(['id', 'name', 'label', 'user_count']);
        });

        //Checks for user groups
        if ($groups->isEmpty()) {
            $io->error(__d('me_cms', 'There are no user groups'));

            return null;
        }

        //Sets header and prints as table
        $header = [I18N_ID, I18N_NAME, I18N_LABEL, I18N_USERS];
        $io->helper('table')->output(array_merge([$header], $groups->toList()));

        return null;
    }
}
