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
$this->extend('/Admin/Common/view');
$this->assign('title', $user->full_name);
$this->append('actions', $this->Html->button(
    I18N_EDIT,
    ['action' => 'edit', $user->id],
    ['class' => 'btn-success', 'icon' => 'pencil']
));

//Only admins can activate accounts and delete users
if ($this->Auth->isGroup('admin')) {
    //If the user is not active (pending)
    if (!$user->active) {
        $this->append('actions', $this->Form->postButton(
            __d('me_cms', 'Activate'),
            ['action' => 'activate', $user->id],
            [
                'class' => 'btn-success',
                'icon' => 'user-plus',
                'confirm' => __d('me_cms', 'Are you sure you want to activate this account?'),
            ]
        ));
    }

    $this->append('actions', $this->Form->postButton(
        I18N_DELETE,
        ['action' => 'delete', $user->id],
        ['class' => 'btn-danger', 'icon' => 'trash-o', 'confirm' => I18N_SURE_TO_DELETE]
    ));
}
?>

<dl class="dl-horizontal">
    <?php
    echo $this->Html->dt(I18N_USERNAME);
    echo $this->Html->dd($user->username);

    echo $this->Html->dt(I18N_EMAIL);
    echo $this->Html->dd($user->email);

    echo $this->Html->dt(I18N_NAME);
    echo $this->Html->dd($user->full_name);

    echo $this->Html->dt(I18N_GROUP);
    echo $this->Html->dd($user->group->label);

    echo $this->Html->dt(I18N_STATUS);

    //If the user is banned
    if ($user->banned) {
        echo $this->Html->dd(__d('me_cms', 'Banned'), ['class' => 'text-danger']);
    //Else, if the user is pending (not active)
    } elseif (!$user->active) {
        echo $this->Html->dd(__d('me_cms', 'Pending'), ['class' => 'text-warning']);
    //Else, if the user is active
    } else {
        echo $this->Html->dd(__d('me_cms', 'Active'), ['class' => 'text-success']);
    }

    if ($user->post_count) {
        echo $this->Html->dt(I18N_POSTS);
        echo $this->Html->dd($this->Html->link(
            $user->post_count,
            ['controller' => 'Posts', 'action' => 'index', '?' => ['user' => $user->id]],
            ['title' => I18N_BELONG_USER]
        ));
    }

    echo $this->Html->dt(__d('me_cms', 'Created'));
    echo $this->Html->dd($user->created->i18nFormat(getConfigOrFail('main.datetime.long')));
    ?>
</dl>

<?php if (!empty($loginLog)) : ?>
    <h4><?= I18N_LAST_LOGIN ?></h4>
    <?= $this->element('admin/last-logins') ?>
<?php endif; ?>
