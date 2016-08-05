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
 * @license	http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use MeCms\Model\Entity\Tag;
use MeCms\Model\Table\AppTable;

/**
 * Tags model
 * @property \Cake\ORM\Association\BelongsToMany $Posts
 */
class TagsTable extends AppTable {
	/**
	 * Name of the configuration to use for this table
	 * @var string|array
	 */
	public $cache = 'posts';
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('tags');
        $this->displayField('tag');
        $this->primaryKey('id');

        $this->belongsToMany('Posts', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'post_id',
            'joinTable' => 'posts_tags',
            'className' => 'MeCms.Posts',
			'through' => 'MeCms.PostsTags',
        ]);
        
        $this->addBehavior('Timestamp');
    }
	
	/**
	 * Gets the tags list
	 * @return array List
	 * @uses $cache
	 */
	public function getList() {
		return $this->find('list')
			->cache('tags_list', $this->cache)
			->toArray();
	}
	
	/**
	 * Build query from filter data
	 * @param Query $query Query object
	 * @param array $data Filter data ($this->request->query)
	 * @return Query $query Query object
	 */
	public function queryFromFilter(Query $query, array $data = []) {
		$query = parent::queryFromFilter($query, $data);
        
		//"Name" field
		if(!empty($data['name']) && strlen($data['name']) > 2) {
			$query->where([
                sprintf('%s.tag LIKE', $this->alias()) => sprintf('%%%s%%', $data['name']),
            ]);
        }
        
		return $query;
	}
	
	/**
	 * Changes tags from string to array
	 * @param string $tags Tags
	 * @return array Tags
	 */
	public function tagsAsArray($tags) {		
		return af(array_map(function($tag) {
			return trim($tag) ? compact('tag') : NULL;
		}, preg_split('/\s*,+\s*/', $tags)));
	}
	
	/**
	 * Changes tags from array to string
	 * @param array $tags Tags
	 * @return string Tags
	 */
	public function tagsAsString(array $tags) {
		return implode(', ', array_map(function($tag) {
			return $tag['tag'];
		}, $tags));
	}

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\TagValidator
     */
    public function validationDefault(Validator $validator) {
		return new \MeCms\Model\Validation\TagValidator;
    }
}