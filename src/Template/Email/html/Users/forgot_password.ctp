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

<?= __d('me_cms', 'Hello {0}', $fullName); ?>,

<?php
    echo __d(
        'me_cms',
        'you have requested to change your password on the site {0}',
        config('main.title')
    );
?>.

<?php
    echo __d(
        'me_cms',
        'To reset your password, click {0}',
        $this->Html->link(__d('me_cms', 'here'), $url)
    );
?>.

<?php
    echo __d(
        'me_cms',
        'If you have not made this request, please contact an administrator'
    );
?>.