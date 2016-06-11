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
    $this->extend('/Common/index');
    
	/**
	 * This template can be used by many actions
	 */
	if($this->request->isAction('index_by_day', 'Posts')) {
        $date = new \Cake\I18n\Time();
        $date->year($this->request->param('year'));
        $date->month($this->request->param('month'));
        $date->day($this->request->param('day'));
		
		if($date->isToday()) {
			$title = __d('me_cms', 'Posts of today');
        }
		elseif($date->isYesterday()) {
			$title = __d('me_cms', 'Posts of yesterday');
        }
		else {
			$title = __d('me_cms', 'Posts of {0}', $date->i18nFormat(config('main.date.long')));
        }
	}
    elseif($this->request->isAction('index_by_month', 'Posts')) {
        $date = new \Cake\I18n\Time();
        $date->year($this->request->param('year'));
        $date->month($this->request->param('month'));
        $date->day(1);
        
        $title = __d('me_cms', 'Posts of {0}', $date->i18nFormat('MMMM y'));
    }
    elseif($this->request->isAction('index_by_year', 'Posts')) {
        $date = new \Cake\I18n\Time();
        $date->year($this->request->param('year'));
        $date->month(1);
        $date->day(1);
        
        $title = __d('me_cms', 'Posts of {0}', $date->i18nFormat('y'));
    }
    
    if(!empty($title)) {
        $this->assign('title', $title);
    }
?>

<?php
    if(!empty($posts)) {
        foreach($posts as $post) {
            echo $this->element('frontend/views/post', compact('post'));
        }

        echo $this->element('MeTools.paginator');
    }
?>