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
?>

<footer class="navbar-fixed-bottom">
    <?php
        $links = [
            $this->Html->link(
                __d('me_cms', 'Search'),
                ['_name' => 'postsSearch']
            ),
            $this->Html->link(
                __d('me_cms', 'Cookies policy'),
                ['_name' => 'page', 'cookies-policy']
            ),
            $this->Html->link(
                __d('me_cms', 'Feed RSS'),
                '/posts/rss'
            ),
        ];

        if (config('default.contact_form')) {
            $links[] = $this->Html->link(
                __d('me_cms', 'Contact us'),
                ['_name' => 'contactForm']
            );
        }

        echo $this->Html->ul($links);
    ?>

    <p>
        <?= __d('me_cms', 'Powered by {0}. Copyright {1}', 'MeCms', date('Y')) ?>
    </p>
    <p>
        <?php
            echo __d(
                'me_cms',
                'Developed by {0} for {1}',
                'Mirko Pagliai',
                $this->Html->link(
                    'Nova Atlantis LTD',
                    'http://novatlantis.it',
                    ['target' => '_blank']
                )
            );
        ?>
    </p>
</footer>