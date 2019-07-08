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
 */
?>

<nav id="userbar" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
    <?= $this->Html->button($this->Html->span(null, ['class' => 'navbar-toggler-icon']), null, [
        'class' => 'navbar-toggler',
        'data-toggle' => 'collapse',
        'data-target' => '#userbarNav',
        'aria-controls' => 'userbarNav',
        'aria-expanded' => 'false',
        'aria-label' => __d('me_cms', 'Toggle navigation'),
    ]) ?>

    <div class="collapse navbar-collapse" id="userbarNav">
        <?php
            echo $this->fetch('content');

            $menu = call_user_func(function () {
                $text = $this->Auth->user('full_name');

                if ($this->Auth->user('picture')) {
                    $text = $this->Thumb->fit($this->Auth->user('picture'), ['height' => 23], ['class' => 'mr-2 rounded-circle']) . $text;
                }

                $this->Dropdown->start($text, ['class' => 'nav-link']);

                if (getConfig('users.login_log')) {
                    echo $this->Html->link(
                        I18N_LAST_LOGIN,
                        ['controller' => 'Users', 'action' => 'lastLogin', 'plugin' => 'MeCms'],
                        ['class' => 'dropdown-item']
                    );
                }

                echo $this->Html->link(
                    __d('me_cms', 'Change picture'),
                    ['controller' => 'Users', 'action' => 'changePicture', 'plugin' => 'MeCms'],
                    ['class' => 'dropdown-item']
                );
                echo $this->Html->link(
                    __d('me_cms', 'Change password'),
                    ['controller' => 'Users', 'action' => 'changePassword', 'plugin' => 'MeCms'],
                    ['class' => 'dropdown-item']
                );
                echo $this->Html->link(__d('me_cms', 'Logout'), ['_name' => 'logout'], ['class' => 'dropdown-item']);

                return $this->Dropdown->end(['class' => 'dropdown-menu-right']);
            });

            echo $this->Html->ul((array)$menu, ['class' => 'navbar-nav'], ['class' => 'dropdown nav-item']);
        ?>
    </div>
</nav>