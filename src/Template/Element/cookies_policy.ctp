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
    //Returns for logged user
    if($this->Auth->isLogged()) {
        return;
    }
    
    //Returns if disabled or already checked
    if(!config('default.cookies_policy') || !empty($_COOKIE['cookies-policy'])) {
        return;
    }
?>

<div id="cookies-policy">
    <div class="container">
        <?= __d('me_cms', 'If you continue, you agree to the use of cookies, ok?') ?>
        <?= $this->Html->button(__d('me_cms', 'Ok'), ['_name' => 'accept_cookies'], ['class' => 'btn-xs btn-success', 'id' => 'cookies-policy-accept']) ?>
        <?= $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'page', 'cookies-policy'], ['class' => 'btn-xs btn-primary']) ?>
    </div>
</div>