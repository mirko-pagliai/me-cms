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
use MeCms\Model\Entity\User;
use MeTools\Console\Command;

/**
 * Lists users
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersCommand extends Command
{
    /**
     * Hook method invoked by CakePHP when a command is about to be executed
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadModel('MeCms.Users');
    }

    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Lists users'));
    }

    /**
     * Internal method to get formatted users data rows
     * @return array
     */
    protected function getUsersRows(): array
    {
        return $this->Users->find()
            ->contain('Groups')
            ->map(function (User $user): array {
                $status = $user->get('active') ? __d('me_cms', 'Active') : __d('me_cms', 'Pending');
                if ($user->get('banned')) {
                    $status = __d('me_cms', 'Banned');
                }

                $created = $user->get('created');
                if (is_object($created) && method_exists($created, 'i18nFormat')) {
                    $created = $created->i18nFormat(FORMAT_FOR_MYSQL);
                }

                return [
                    (string)$user->get('id'),
                    $user->get('username'),
                    $user->get('group')->get('label') ?: $user->get('group'),
                    $user->get('full_name'),
                    $user->get('email'),
                    (string)$user->get('post_count'),
                    $status,
                    $created,
                ];
            })->toList();
    }

    /**
     * Lists users
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $rows = $this->getUsersRows();
        if (!$rows) {
            return $io->error(__d('me_cms', 'There are no users'));
        }

        $rows = array_merge([[I18N_ID, I18N_USERNAME, I18N_GROUP, I18N_NAME, I18N_EMAIL, I18N_POSTS, I18N_STATUS, I18N_DATE]], $rows);
        $io->helper('table')->output($rows);

        return null;
    }
}
