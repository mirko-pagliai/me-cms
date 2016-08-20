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

<?php if (!empty($loginLog)) : ?>
    <table class="table table-hover">
        <tr>
            <th class="text-center"><?= __d('me_cms', 'Time') ?></th>
            <th class="text-center min-width"><?= __d('me_cms', 'IP') ?></th>
            <th class="text-center"><?= __d('me_cms', 'Browser') ?></th>
            <th><?= __d('me_cms', 'Client') ?></th>
        </tr>
        <?php foreach ($loginLog as $log) : ?>
            <tr>
                <td class="text-center">
                    <?= $log->time ?>
                </td>
                <td class="text-center">
                    <?= $log->ip ?>
                </td>
                <td class="text-center">
                    <?php
                        echo __d(
                            'me_cms',
                            '{0} on {1}',
                            $log->browser,
                            $log->platform
                        );
                    ?>
                </td>
                <td>
                    <?= $log->agent ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>