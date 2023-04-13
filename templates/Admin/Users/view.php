<?php /** @noinspection PhpUnhandledExceptionInspection */
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
 *
 * @var \MeCms\View\View\Admin\AppView $this
 * @var \MeCms\Model\Entity\User $user
 */

$this->extend('/Admin/common/view');
$this->assign('title', $user->get('full_name'));
$this->append('actions', $this->Html->button(
    I18N_EDIT,
    ['action' => 'edit', $user->get('id')],
    ['class' => 'btn-success', 'icon' => 'pencil-alt']
));

//Only admins can activate accounts and delete users
if ($this->Identity->isGroup('admin')) {
    //If the user is not active (pending)
    if (!$user->get('active')) {
        $this->append('actions', $this->Form->postButton(
            __d('me_cms', 'Activate'),
            ['action' => 'activate', $user->get('id')],
            [
                'class' => 'btn-success',
                'icon' => 'user-plus',
                'confirm' => __d('me_cms', 'Are you sure you want to activate this account?'),
            ]
        ));
    }

    $this->append('actions', $this->Form->postButton(
        I18N_DELETE,
        ['action' => 'delete', $user->get('id')],
        ['class' => 'btn-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE]
    ));
}
?>

<div class="mb-4">
    <?= $this->Thumb->fit($user->get('picture'), ['height' => 150], ['class' => 'rounded-circle']) ?>
</div>

<dl class="row">
    <dt class="col-1"><?= I18N_USERNAME ?></dt>
    <dd class="col-11"><?= $user->get('username') ?></dd>

    <dt class="col-1"><?= I18N_EMAIL ?></dt>
    <dd class="col-11"><?= $this->Html->link($user->get('email'), sprintf('mailto:%s', $user->get('email'))) ?></dd>

    <dt class="col-1"><?= I18N_NAME ?></dt>
    <dd class="col-11"><?= $user->get('full_name') ?></dd>

    <dt class="col-1"><?= I18N_GROUP ?></dt>
    <dd class="col-11">
        <?= $this->Html->link(
            $user->get('group')->get('label'),
            ['action' => 'index', '?' => ['group' => $user->get('group')->get('id')]],
            ['title' => I18N_BELONG_ELEMENT]
        ) ?>
    </dd>

    <dt class="col-1"><?= I18N_STATUS ?></dt>
    <dd class="col-11">
        <?php
        $class = 'text-success';
        //If it's banned
        if ($user->get('banned')) {
            $text = __d('me_cms', 'Banned');
            $class = 'text-danger';
        //Else, if it's pending (not active)
        } elseif (!$user->get('active')) {
            $text = __d('me_cms', 'Pending');
            $class = 'text-warning';
        }

        echo $this->Html->span($text ?? __d('me_cms', 'Active'), compact('class'));
        ?>
    </dd>

    <?php if ($user->get('post_count')) : ?>
        <dt class="col-1"><?= I18N_POSTS ?></dt>
        <dd class="col-11">
            <?= $this->Html->link(
                (string)$user->get('post_count'),
                ['controller' => 'Posts', 'action' => 'index', '?' => ['user' => $user->get('id')]],
                ['title' => I18N_BELONG_USER]
            ) ?>
        </dd>
    <?php endif; ?>

    <dt class="col-1"><?= __d('me_cms', 'Created') ?></dt>
    <dd class="col-11"><?= $user->get('created')->i18nFormat() ?></dd>
</dl>

<?php if (!empty($loginLog)) : ?>
    <h4><?= I18N_LAST_LOGIN ?></h4>
    <?= $this->element('admin/last-logins') ?>
<?php endif; ?>
