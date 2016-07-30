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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
    $this->extend('/Admin/Common/view');
    $this->assign('title', __d('me_cms', 'User {0}', $user->full_name));
?>

<?php
    $actions = [
        $this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $user->id], ['icon' => 'pencil']),
    ];

    //Only admins can activate accounts and delete users
    if($this->Auth->isGroup('admin')) {
        //If the user is not active (pending)
        if(!$user->active) {
            $actions[] = $this->Form->postLink(__d('me_cms', 'Activate'), ['action' => 'activate_account', $user->id], ['icon' => 'user-plus', 'confirm' => __d('me_cms', 'Are you sure you want to activate this account?')]);
        }
        
        $actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $user->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
    }

    echo $this->Html->ul($actions, ['class' => 'actions']);
?>

<dl class="dl-horizontal">
    <?php
        echo $this->Html->dt(__d('me_cms', 'Username'));
        echo $this->Html->dd($user->username);

        echo $this->Html->dt(__d('me_cms', 'Email'));
        echo $this->Html->dd($user->email);

        echo $this->Html->dt(__d('me_cms', 'Name'));
        echo $this->Html->dd($user->full_name);

        echo $this->Html->dt(__d('me_cms', 'Group'));
        echo $this->Html->dd($user->group->label);

        echo $this->Html->dt(__d('me_cms', 'Status'));

        //If the user is banned
        if($user->banned) {
            echo $this->Html->dd(__d('me_cms', 'Banned'), ['class' => 'text-danger']);
        }
        //Else, if the user is pending (not active)
        elseif(!$user->active) {
            echo $this->Html->dd(__d('me_cms', 'Pending'), ['class' => 'text-warning']);
        }
        //Else, if the user is active
        else {
            echo $this->Html->dd(__d('me_cms', 'Active'), ['class' => 'text-success']);
        }
        
        if($user->post_count) {
            echo $this->Html->dt(__d('me_cms', 'Posts'));
            echo $this->Html->dd($user->post_count);
        }

        echo $this->Html->dt(__d('me_cms', 'Created'));
        echo $this->Html->dd($user->created->i18nFormat(config('main.datetime.long')));
    ?>
</dl>

<?php if(!empty($loginLog)): ?>
    <h4><?= __d('me_cms', 'Last login') ?></h4>

    <table class="table table-hover">
        <tr>
            <th class="text-center"><?= __d('me_cms', 'Time') ?></th>
            <th class="text-center min-width"><?= __d('me_cms', 'IP') ?></th>
            <th class="text-center"><?= __d('me_cms', 'Browser') ?></th>
            <th><?= __d('me_cms', 'Client') ?></th>
        </tr>
        <?php foreach($loginLog as $log): ?>
            <tr>
                <td class="text-center">
                    <?= $log->time ?>
                </td>
                <td class="text-center">
                    <?= $log->ip ?>
                </td>
                <td class="text-center">
                    <?= __d('me_cms', '{0} on {1}', $log->browser, $log->platform) ?>
                </td>
                <td>
                    <?= $log->agent ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>