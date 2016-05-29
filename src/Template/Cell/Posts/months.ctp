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
	if(empty($months) || count($months) < 2) {
		return;
    }
    
	//Extends the widget common view
	$this->extend('/Common/widget');
	$this->assign('title', __d('me_cms', 'Posts by month'));
    
	echo $this->Form->create(FALSE, ['type' => 'get', 'url' => ['_name' => 'posts_by_month', date('Y'), date('m')]]);
	echo $this->Form->input('q', [
		'label' => FALSE,
		'onchange' => 'send_form(this)',
		'options' => $months,
	]);
	echo $this->Form->end();
?>