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
 * @license	http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use MeCms\Model\Entity\Tag;

/**
 * Tags model
 */
class TagsTable extends Table {
    /**
     * Initialize method
     * @param array $config The table configuration
     */
    public function initialize(array $config) {
        $this->table('tags');
        $this->displayField('tag');
        $this->primaryKey('id');
        $this->belongsToMany('Posts', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'post_id',
            'joinTable' => 'posts_tags',
            'className' => 'MeCms.Posts',
			'through' => 'MeCms.PostsTags'
        ]);
    }
	
	/**
	 * Gets the tags list
	 * @return array List
	 */
	public function getList() {
		return $this->find('list')
			->cache('tags_list', 'posts')
			->toArray();
	}
	
	/**
	 * Changes tags from string to array
	 * @param string $tags Tags
	 * @return array Tags
	 */
	public function tagsAsArray($tags) {
		return array_filter(array_map(function($tag) {
			return trim($tag) ? compact('tag') : NULL;
		}, preg_split('/[\s]+/', $tags)));
	}
	
	/**
	 * Changes tags from array to string
	 * @param array $tags Tags
	 * @return string Tags
	 */
	public function tagsAsString(array $tags) {
		return implode(' ', array_map(function($tag) {
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