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
use MeTools\Console\Command;

/**
 * Adds an user
 */
class AddUserCommand extends Command
{
    /**
     * Hook method for defining this command's option parser
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser->setDescription(__d('me_cms', 'Adds an user'));
        $parser->addOption('group', [
            'short' => 'g',
            'help' => __d('me_cms', 'Group ID'),
        ]);

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
     * Adds an user
     * @param Arguments $args The command arguments
     * @param ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $groups = $this->Users->Groups->find('list');

        if ($groups->isEmpty()) {
            $io->err(__d('me_cms', 'Before you can manage users, you have to create at least a user group'));
            $this->abort();
        }

        $user = [];

        //Asks for some fields
        $user['username'] = $this->in(I18N_USERNAME);
        $user['password'] = $this->in(I18N_PASSWORD);
        $user['password_repeat'] = $this->in(I18N_REPEAT_PASSWORD);
        $user['email'] = $this->in(I18N_EMAIL);
        $user['first_name'] = $this->in(I18N_FIRST_NAME);
        $user['last_name'] = $this->in(I18N_LAST_NAME);
        $user['group_id'] = $args->getOption('group');

        //Asks for group, if not passed as option
        if (!$user['group_id']) {
            //Formats groups
            foreach ($groups as $id => $group) {
                $groups[$id] = [$id, $group];
            }

            //Sets header and prints as table
            $header = ['ID', 'Name'];
            $io->helper('table')->output(array_merge([$header], $groups));

            $user['group_id'] = $this->in(__d('me_cms', 'Group ID'));
        }

        //Checks the group IDs
        if (!array_key_exists($user['group_id'], $groups)) {
            $io->err(__d('me_cms', 'Invalid group ID'));
            $this->abort();
        }

        //Checks fields
        foreach ($user as $key => $value) {
            if (empty($value)) {
                $io->err(__d('me_cms', 'Field `{0}` is empty. Try again', $key));
                $this->abort();
            }
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
                        $io->err(__d('me_cms', 'Field `{0}`: {1}', $field, lcfirst($error)));
                    }
                }
            }

            $this->abort();
        }

        $io->success(I18N_OPERATION_OK);
        $io->success(__d('me_cms', 'The user was created with ID {0}', $user->id));

        return null;
    }
}
