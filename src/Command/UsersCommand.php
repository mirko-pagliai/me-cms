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
use Cake\ORM\Query;
use MeCms\Model\Entity\User;
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
        $parser->setDescription(__d('me_cms', 'Lists users'));

        return $parser;
    }

    /**
     * Hook method invoked by CakePHP when a command is about to be executed
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadModel('MeCms.Users');
    }

    /**
     * Lists users
     * @param Arguments $args The command arguments
     * @param ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        //Gets users
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

            return [
                $user->id,
                $user->username,
                $user->group->label,
                $user->full_name,
                $user->email,
                $user->post_count,
                $user->status,
                $user->created->i18nFormat('yyyy/MM/dd HH:mm'),
            ];
        });

        if ($users->isEmpty()) {
            $io->error(__d('me_cms', 'There are no users'));
            $this->abort();
        }

        //Sets headers and prints as table
        $headers = [I18N_ID, I18N_USERNAME, I18N_GROUP, I18N_NAME, I18N_EMAIL, I18N_POSTS, I18N_STATUS, I18N_DATE];
        $io->helper('table')->output(array_merge([$headers], $users->toList()));

        return null;
    }
}
