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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;

/**
 * Application table class
 */
class AppTable extends Table {
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 * @uses Cake\I18n\Time::i18nFormat()
	 */
	public function findActive(Query $query, array $options) {
        $query->where([
            sprintf('%s.active', $this->alias())		=> TRUE,
			sprintf('%s.created <=', $this->alias())	=> (new Time())->i18nFormat(FORMAT_FOR_MYSQL)
        ]);
		
        return $query;
    }
	
	/**
	 * "Random" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 * @uses Cake\I18n\Time::i18nFormat()
	 */
	public function findRandom(Query $query, array $options) {
		$query->order('rand()');
		
		if(!$query->clause('limit'))
			$query->limit(1);
		
        return $query;
    }
	
	/**
	 * Gets conditions from a filter form
	 * @param array $query Query (`$this->request->query`)
	 * @return array Conditions
	 * @uses Cake\I18n\Time::addMonth()
	 * @uses Cake\I18n\Time::i18nFormat()
	 */
	public function fromFilter(array $query) {
		if(empty($query))
			return [];
		
		//"Title" field
		if(!empty($query['title'])) {
			$conditions[sprintf('%s.title LIKE', $this->alias())] = sprintf('%%%s%%', $query['title']);
		}
		
		//"Filename" field
		if(!empty($query['filename'])) {
			$conditions[sprintf('%s.filename LIKE', $this->alias())] = sprintf('%%%s%%', $query['filename']);
		}
		
		//"Active" field
		if(!empty($query['active'])) {
			if($query['active'] === 'yes')
				$conditions[sprintf('%s.active', $this->alias())] = TRUE;
			elseif($query['active'] === 'no')
				$conditions[sprintf('%s.active', $this->alias())] = FALSE;
		}
		
		//"Priority" field
		if(!empty($query['priority'])) {
			$conditions[sprintf('priority', $this->alias())] = $query['priority'];
		}
		
		//"Created" field
		if(!empty($query['created']) && preg_match('/^[1-9][0-9]{3}\-[0-1][0-9]$/', $query['created'])) {			
			//Sets the start date
			$start = new Time(sprintf('%s-1 00:00:00', $query['created']));
			$conditions[sprintf('created >=', $this->alias())] = $start->i18nFormat(FORMAT_FOR_MYSQL);
			
			//Sets the end date
			$conditions[sprintf('created <', $this->alias())] = $start->addMonth(1)->i18nFormat(FORMAT_FOR_MYSQL);
		}
		
		return empty($conditions) ? [] : $conditions;
	}
	
	/**
	 * Checks whether an object (a record) belongs to an user.
	 * 
	 * For example:
	 * <code>
	 * $posts = TableRegistry::get('Posts');
	 * $posts->isOwnedBy(2, 4);
	 * </code>
	 * checks if the posts with ID 2 belongs to the user with ID 4.
	 * @param int $id Object id
	 * @param int $user_id User id
	 * @return bool TRUE if it belongs to the user, otherwise FALSE
	 */
	public function isOwnedBy($id, $user_id = NULL) {
		if(empty($user_id))
			return FALSE;
		
		return (bool) $this->find('all')->where(compact('id', 'user_id'))->count();
	}
}