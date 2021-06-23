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
use MeTools\Console\Command;

/**
 * Adds an user
 */
class AddUserCommand extends Command
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Hook method for defining this command's option parser
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription(__d('me_cms', 'Adds an user'))
            ->addOption('group', ['short' => 'g', 'help' => __d('me_cms', 'Group ID')]);
    }

    /**
     * Adds an user
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->loadModel('MeCms.Users');

        $groups = $this->Users->Groups->find('list');
        if ($groups->isEmpty()) {
            return $io->error(__d('me_cms', 'Before you can manage users, you have to create at least a user group'));
        }
        $groups = $groups->toList();

        //Asks for some fields
        $user = [];
        foreach ([
            'username' => I18N_USERNAME,
            'password' => I18N_PASSWORD,
            'password_repeat' => I18N_REPEAT_PASSWORD,
            'email' => I18N_EMAIL,
            'first_name' => I18N_FIRST_NAME,
            'last_name' => I18N_LAST_NAME,
        ] as $field => $question) {
            $user[$field] = $io->ask($question);
        }

        //Asks for group, if not passed as option
        $user['group_id'] = $args->getOption('group');
        if (!$user['group_id']) {
            //Formats groups
            foreach ($groups as $id => $group) {
                $groups[$id] = [(string)$id, $group];
            }

            //Sets headers and prints as table
            $io->helper('table')->output(array_merge([['ID', 'Name']], $groups));
            $user['group_id'] = $io->ask(__d('me_cms', 'Group ID'));
        }

        //Checks fields
        foreach ($user as $key => $value) {
            if (empty($value)) {
                return $io->error(__d('me_cms', 'Field `{0}` is empty. Try again', $key));
            }
        }

        //Checks the group IDs
        if (!array_key_exists((string)$user['group_id'], $groups)) {
            return $io->error(__d('me_cms', 'Invalid group ID'));
        }

        //Saves the user
        $user = $this->Users->newEntity($user);
        if (!$this->Users->save($user)) {
            $io->err(I18N_OPERATION_NOT_OK);
            $io->err(__d('me_cms', 'The user could not be saved'));

            //With verbose, shows errors for each field
            if ($args->getOption('verbose')) {
                foreach ($user->getErrors() as $field => $errors) {
                    foreach ($errors as $error) {
                        $io->error(__d('me_cms', 'Field `{0}`: {1}', $field, lcfirst($error)));
                    }
                }
            }

            return null;
        }

        $io->success(I18N_OPERATION_OK);
        $io->success(__d('me_cms', 'The user was created with ID {0}', $user->get('id')));

        return null;
    }
}
