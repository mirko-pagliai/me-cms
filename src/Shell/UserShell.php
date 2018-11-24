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
 */
namespace MeCms\Shell;

use Cake\ORM\Query;
use MeCms\Model\Entity\UsersGroup;
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
     * @return bool `false` on failure
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
        $user['username'] = $this->in(I18N_USERNAME);
        $user['password'] = $this->in(I18N_PASSWORD);
        $user['password_repeat'] = $this->in(I18N_REPEAT_PASSWORD);
        $user['email'] = $this->in(I18N_EMAIL);
        $user['first_name'] = $this->in(I18N_FIRST_NAME);
        $user['last_name'] = $this->in(I18N_LAST_NAME);

        //Asks for group, if not passed as option
        if (!$this->param('group')) {
            //Formats groups
            foreach ($groups as $id => $group) {
                $groups[$id] = [$id, $group];
            }

            //Sets header
            $header = ['ID', 'Name'];

            //Prints as table
            $this->helper('table')->output(array_merge([$header], $groups));

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
            $this->err(I18N_OPERATION_NOT_OK);
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

        $this->success(I18N_OPERATION_OK);
        $this->success(__d('me_cms', 'The user was created with ID {0}', $user->id));
    }

    /**
     * Lists user groups
     * @return bool `false` on failure
     */
    public function groups()
    {
        //Gets user groups
        $groups = $this->Users->Groups->find();

        //Checks for user groups
        if (!$groups->count()) {
            $this->err(__d('me_cms', 'There are no user groups'));

            return false;
        }

        //Formats groups
        $groups = collection($groups)->extract(function (UsersGroup $group) {
            return [
                $group->id,
                $group->name,
                $group->label,
                $group->user_count,
            ];
        })->toList();

        //Sets header
        $header = [I18N_ID, I18N_NAME, I18N_LABEL, I18N_USERS];

        //Prints as table
        $this->helper('table')->output(array_merge([$header], $groups));
    }

    /**
     * Lists users
     * @return bool `false` on failure
     */
    public function users()
    {
        //Gets users
        $users = $this->Users->find()->contain('Groups', function (Query $q) {
            return $q->select(['label']);
        });

        //Checks for users
        if (!$users->count()) {
            $this->err(__d('me_cms', 'There are no users'));

            return false;
        }

        //Sets headers
        $headers = [I18N_ID, I18N_USERNAME, I18N_GROUP, I18N_NAME, I18N_EMAIL, I18N_POSTS, I18N_STATUS, I18N_DATE];

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
        $this->helper('table')->output(array_merge([$headers], $users));
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
