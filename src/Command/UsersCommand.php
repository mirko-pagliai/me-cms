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
use Cake\I18n\Time;
use Cake\ORM\Query;
use MeCms\Model\Entity\User;
use MeCms\Model\Entity\UsersGroup;
use MeTools\Console\Command;

/**
 * Lists users
 */
class UsersCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        return $parser->setDescription(__d('me_cms', 'Lists users'));
    }

    /**
     * Lists users
     * @param Arguments $args The command arguments
     * @param ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadModel('MeCms.Users');

        $users = $this->Users->find()->contain('Groups', function (Query $q) {
            return $q->select(['label']);
        })->map(function (User $user) {
            if ($user->banned) {
                $user->status = __d('me_cms', 'Banned');
            } elseif (!$user->active) {
                $user->status = __d('me_cms', 'Pending');
            } else {
                $user->status = __d('me_cms', 'Active');
            }

            $user->created = $user->created instanceof Time ? $user->created->i18nFormat('yyyy/MM/dd HH:mm') : $user->created;
            $user->group = $user->group instanceof UsersGroup ? $user->group->label : $user->group;

            return $user->extract(['id', 'username', 'group', 'full_name', 'email', 'post_count', 'status', 'created']);
        });

        if ($users->isEmpty()) {
            $io->error(__d('me_cms', 'There are no users'));

            return null;
        }

        //Sets headers and prints as table
        $headers = [I18N_ID, I18N_USERNAME, I18N_GROUP, I18N_NAME, I18N_EMAIL, I18N_POSTS, I18N_STATUS, I18N_DATE];
        $io->helper('table')->output(array_merge([$headers], $users->toList()));

        return null;
    }
}
