<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Shell;

use MeTools\Console\Shell;

/**
 * Allows the user management
 */
class UserShell extends Shell
{
    /**
     * Initialize
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        //Loads the Users model
        $this->loadModel('MeCms.Users');
    }

    /**
     * Adds an user
     * @return int|bool User ID or `false` on failure
     */
    public function add()
    {
        //Gets user groups
        $groups = $this->Users->Groups->find('list')->toArray();

        //Checks for user groups
        if (empty($groups)) {
            $this->err(__d('me_cms', 'Before you can manage users, you have to create at least a user group'));

            return false;
        }

        $user = [];

        //Asks for some fields
        $user['username'] = $this->in(__d('me_cms', 'Username'));
        $user['password'] = $this->in(__d('me_cms', 'Password'));
        $user['password_repeat'] = $this->in(__d('me_cms', 'Repeat password'));
        $user['email'] = $this->in(__d('me_cms', 'Email'));
        $user['first_name'] = $this->in(__d('me_cms', 'First name'));
        $user['last_name'] = $this->in(__d('me_cms', 'Last name'));

        //Asks for group, if not passed as option
        if (!$this->param('group')) {
            //Formats groups
            foreach ($groups as $id => $group) {
                $groups[$id] = [$id, $group];
            }

            //Sets header
            $header = ['ID', 'Name'];

            //Prints as table
            $this->helper('table')->output(am([$header], $groups));

            $user['group_id'] = $this->in(__d('me_cms', 'Group ID'));
        } else {
            $user['group_id'] = $this->param('group');
        }

        //Checks fields
        foreach ($user as $key => $value) {
            if (empty($value)) {
                $this->err(__d('me_cms', 'Field `{0}` is empty. Try again', $key));

                return false;
            }
        }

        //Checks the group IDs
        if (!array_key_exists($user['group_id'], $groups)) {
            $this->err(__d('me_cms', 'Invalid group ID'));

            return false;
        }

        $user = $this->Users->newEntity($user);

        //Saves the user
        if (!$this->Users->save($user)) {
            $this->err(__d('me_cms', 'An error occurred, try again'));
            $this->err(__d('me_cms', 'The user could not be saved'));

            //With verbose, shows errors for each field
            if ($this->param('verbose')) {
                foreach ($user->getErrors() as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->err(__d('me_cms', 'Field `{0}`: {1}', $field, lcfirst($error)));
                    }
                }
            }

            return false;
        }

        $this->success(__d('me_cms', 'The user has been saved'));

        return $user->id;
    }

    /**
     * Lists user groups
     * @return void
     */
    public function groups()
    {
        //Gets user groups
        $groups = $this->Users->Groups->find()
            ->select(['id', 'name', 'label', 'user_count']);

        //Checks for user groups
        if (!$groups->count()) {
            $this->err(__d('me_cms', 'There are no user groups'));

            return;
        }

        //Formats groups
        $groups = collection($groups)->extract(function ($group) {
            return [
                $group->id,
                $group->name,
                $group->label,
                $group->user_count,
            ];
        })->toList();

        //Sets header
        $header = [
            __d('me_cms', 'ID'),
            __d('me_cms', 'Name'),
            __d('me_cms', 'Label'),
            __d('me_cms', 'Users')
        ];

        //Prints as table
        $this->helper('table')->output(am([$header], $groups));
    }

    /**
     * Lists users
     * @return void
     */
    public function users()
    {
        //Gets users
        $users = $this->Users->find()
            ->select(['id', 'username', 'email', 'first_name', 'last_name', 'active', 'banned', 'post_count', 'created'])
            ->contain(['Groups' => ['fields' => ['label']]]);

        //Checks for users
        if (!$users->count()) {
            $this->err(__d('me_cms', 'There are no users'));

            return;
        }

        //Sets header
        $header = [
            __d('me_cms', 'ID'),
            __d('me_cms', 'Username'),
            __d('me_cms', 'Group'),
            __d('me_cms', 'Name'),
            __d('me_cms', 'Email'),
            __d('me_cms', 'Posts'),
            __d('me_cms', 'Status'),
            __d('me_cms', 'Date'),
        ];

        $users = collection($users)->map(function ($user) {
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
        })->toList();

        //Prints as table
        $this->helper('table')->output(am([$header], $users));
    }

    /**
     * Gets the option parser instance and configures it
     * @return ConsoleOptionParser
     * @uses MeTools\Shell\InstallShell::getOptionParser()
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription(__d('me_cms', 'Shell to handle users and user groups'));

        $parser->addSubcommand('add', [
            'help' => __d('me_cms', 'Adds an user'),
            'parser' => ['options' => [
                'group' => [
                    'short' => 'g',
                    'help' => __d('me_cms', 'Group ID'),
                ],
            ]],
        ]);
        $parser->addSubcommand('groups', ['help' => __d('me_cms', 'Lists user groups')]);
        $parser->addSubcommand('users', ['help' => __d('me_cms', 'Lists users')]);

        return $parser;
    }
}
