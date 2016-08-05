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
namespace MeCms\Model\Table;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;

/**
 * Application table class
 */
class AppTable extends Table {
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
     * @uses $cache
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		if(!empty($this->cache)) {
			Cache::clear(FALSE, $this->cache);
        }
	}
	
	/**
	 * Called after an entity is saved
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
     * @uses $cache
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		if(!empty($this->cache)) {
			Cache::clear(FALSE, $this->cache);
        }
	}
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findActive(Query $query, array $options) {
        $query->where([
            sprintf('%s.active', $this->alias()) => TRUE,
			sprintf('%s.created <=', $this->alias()) => new Time(),
        ]);
		
        return $query;
    }
	
	/**
	 * "Random" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findRandom(Query $query, array $options) {
		$query->order('rand()');
		
		if(!$query->clause('limit')) {
			$query->limit(1);
        }
		
        return $query;
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
	 * @param int $id Object ID
	 * @param int $user_id User ID
	 * @return bool
	 */
	public function isOwnedBy($id, $user_id = NULL) {
		if(empty($user_id)) {
			return FALSE;
        }
		
		return (bool) $this->find('all')
            ->where(compact('id', 'user_id'))
            ->count();
	}
	
	/**
	 * Build query from filter data
	 * @param Query $query Query object
	 * @param array $data Filter data ($this->request->query)
	 * @return Query $query Query object
	 */
	public function queryFromFilter(Query $query, array $data = []) {
        //"ID" field
        if(!empty($data['id']) && is_positive($data['id'])) {
			$query->where([
                sprintf('%s.id', $this->alias()) => $data['id'],
            ]);
        }
        
		//"Title" field
		if(!empty($data['title']) && strlen($data['title']) > 2) {
			$query->where([
                sprintf('%s.title LIKE', $this->alias()) => sprintf('%%%s%%', $data['title']),
            ]);
        }
        
		//"Filename" field
		if(!empty($data['filename']) && strlen($data['filename']) > 2) {
			$query->where(
                [sprintf('%s.filename LIKE', $this->alias()) => sprintf('%%%s%%', $data['filename']),
            ]);
        }
        
		//"User" (author) field
		if(!empty($data['user']) && preg_match('/^[1-9]\d*$/', $data['user'])) {
			$query->where([
                sprintf('%s.user_id', $this->alias()) => $data['user'],
            ]);
        }
        
		//"Category" field
		if(!empty($data['category']) && preg_match('/^[1-9]\d*$/', $data['category'])) {
			$query->where([
                sprintf('%s.category_id', $this->alias()) => $data['category'],
            ]);
        }
        
		//"Active" field
		if(!empty($data['active'])) {
            $query->where([
                sprintf('%s.active', $this->alias()) => $data['active'] === 'yes',
            ]);
        }
        
		//"Priority" field
		if(!empty($data['priority']) && preg_match('/^[1-5]$/', $data['priority'])) {
			$query->where([
                sprintf('%s.priority', $this->alias()) => $data['priority'],
            ]);
        }
        
		//"Created" field
		if(!empty($data['created']) && preg_match('/^[1-9][0-9]{3}\-[0-1][0-9]$/', $data['created'])) {
            $start = new Time(sprintf('%s-01', $data['created']));
            $end = (new Time($start))->addMonth(1);
            
			$query->where([
				sprintf('%s.created >=', $this->alias()) => $start,
				sprintf('%s.created <', $this->alias()) => $end,
			]);
        }
        
		return $query;
	}
}