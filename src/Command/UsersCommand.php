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

use Cake\Collection\CollectionInterface;
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
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Lists users'));
    }

    /**
     * Get formatted users data as rows
     * @return string[][]
     */
    public function getUsersRows(): array
    {
        $this->getTableLocator()->clear();
        $Users = $this->getTableLocator()->get('MeCms.Users');

        return $Users->find()
            ->contain('Groups')
            ->formatResults(fn(CollectionInterface $results): CollectionInterface => $results->map(function (User $user): array {
                $result = array_map(fn(string $key): string => (string)$user->get($key), ['id', 'username', 'full_name', 'email', 'post_count', 'created']);
                $result['group'] = $user->get('group')->get('label') ?: $user->get('group');
                $result['status'] = $user->get('banned') ? __d('me_cms', 'Banned') : ($user->get('active') ? __d('me_cms', 'Active') : __d('me_cms', 'Pending'));

                return $result;
            }))
            ->orderAsc('Users.id')
            ->all()
            ->toList();
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

        $rows = [[I18N_ID, I18N_USERNAME, I18N_GROUP, I18N_NAME, I18N_EMAIL, I18N_POSTS, I18N_STATUS, I18N_DATE], ...$rows];
        $io->helper('table')->output($rows);

        return null;
    }
}
