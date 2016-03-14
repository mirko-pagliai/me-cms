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

use MeTools\Cache\Cache;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\AppTable;

/**
 * Posts model
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 */
class PostsTable extends AppTable {
	/**
	 * Name of the configuration to use for this table
	 * @var string|array
	 */
	public $cache = 'posts';
	
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses MeCms\Model\Table\AppTable::afterDelete()
	 * @uses setNextToBePublished()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		parent::afterDelete($event, $entity, $options);
		
		//Sets the next post to be published
		$this->setNextToBePublished();	
	}
	
	/**
	 * Called after an entity is saved.
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses MeCms\Model\Table\AppTable::afterSave()
	 * @uses setNextToBePublished()
	 */
	public function afterSave(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		parent::afterSave($event, $entity, $options);
		
		//Sets the next post to be published
		$this->setNextToBePublished();
	}

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        return $rules;
    }
	
	/**
	 * Builds tags for the request data.
	 * For each tag, it searches if the tag already exists in the database.
	 * If a tag exists in the database, it sets that tag as ID
	 * @param array $requestData Request data from form (`$this->request->data`)
	 * @return array Request data
	 * @uses MeCms\Model\Table\TagsTable::getList()
	 * @uses MeCms\Model\Table\TagsTable::tagsAsArray()
	 */
	public function buildTagsForRequestData($requestData) {
		$tags = $this->Tags->tagsAsArray($requestData['tags']);

		//Gets tags from database
		$tagsFromDb = $this->Tags->getList();
		
		//For each tag, it searches if the tag already exists in the database.
		//If a tag exists in the database, it sets that tag as ID
		foreach($tags as $k => $tag)
			if(is_int($id = array_search($tag['tag'], $tagsFromDb)))
				$tags[$k] = compact('id');
		
		return am($requestData, compact('tags'));
	}
	
	/**
	 * Checks if the cache is valid.
	 * If the cache is not valid, it empties the cache.
	 * @uses getNextToBePublished()
	 * @uses setNextToBePublished()
	 */
	public function checkIfCacheIsValid() {
		//Gets from cache the timestamp of the next record to be published
		$next = $this->getNextToBePublished();
		
		//If the cache is not valid, it empties the cache
		if($next && time() >= $next) {
			Cache::clear(FALSE, 'posts');
		
			//Sets the next record to be published
			$this->setNextToBePublished();
		}
	}
	
	/**
	 * Gets from cache the timestamp of the next record to be published.
	 * This value can be used to check if the cache is valid
	 * @return int Timestamp
	 * @see checkIfCacheIsValid()
	 * @uses $cache
	 */
	public function getNextToBePublished() {
		return Cache::read('next_to_be_published', $this->cache);
	}
	
	/**
	 * Gets the related posts for a post
	 * @param \MeCms\Model\Entity\Post $post Post entity. It must contain `Tags`
	 * @param int $limit Limit of related posts
	 * @param bool $images If TRUE, gets only posts with images
	 * @return array Related posts, array of entities
	 */
	public function getRelated(\MeCms\Model\Entity\Post $post, $limit = 5, $images = TRUE) {
		if(empty($post->tags))
			return;
		
		//Tries to gets related posts from cache
		$related = Cache::read($cache = sprintf('related_%s_posts_for_%s', $limit, $post->id), $this->cache);
				
		if(empty($related) && !is_null($related)) {
			$tags = $post->tags;

			//Re-orders tags, using the "post_count" field, then based on the popularity of tags
			usort($tags, function($a, $b) { return $b['post_count'] - $a['post_count']; });

			//Limits tags
			if(count($tags) > $limit)
				$tags = array_slice($tags, 0 , $limit);

			//This array will be contain the ID to be excluded
			$exclude = [$post->id];

			//Gets a related post for each tag
			//Reveres the tags order, because the tags less popular have less chance to find a related post
			foreach(array_reverse($tags) as $tag) {
				$post = $this->find('active')
					->select(['id', 'title', 'slug', 'text'])
					->matching('Tags', function($q) use($tag) {
						return $q->where([sprintf('%s.id', $this->Tags->alias()) => $tag->id]);
					})
					->where([sprintf('%s.id NOT IN', $this->alias()) => $exclude]);

				if($images)			
					$post->where([sprintf('%s.text LIKE', $this->alias()) => sprintf('%%%s%%', '<img')]);		
					
				$post = $post->first();

				//Adds the post to the related posts and its ID to the IDs to be excluded for the next query
				if(!empty($post->id)) {
					$related[] = $post;
					$exclude[] = $post->id;
				}
			}
			
			Cache::write($cache, $related ? $related : NULL, $this->cache);
		}
		
		return $related;
	}
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('posts');
        $this->displayField('title');
        $this->primaryKey('id');
		
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.PostsCategories'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.Users'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'post_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'posts_tags',
            'className' => 'MeCms.Tags',
			'through' => 'MeCms.PostsTags'
        ]);

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Categories' => ['post_count'], 'Users' => ['post_count']]);
    }
	
	/**
	 * Build query from filter data
	 * @param Query $query Query object
	 * @param array $data Filter data ($this->request->query)
	 * @return Query $query Query object
	 * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
	 */
	public function queryFromFilter(Query $query, array $data = []) {
		$query = parent::queryFromFilter($query, $data);
		
		//"Tag" field
		if(!empty($data['tag']) && strlen($data['tag']) > 2)
			$query->matching('Tags', function($q) use ($data) {
				return $q->where([sprintf('%s.tag', $this->Tags->alias()) => $data['tag']]);
			});
		
		return $query;
	}
	
	/**
	 * Sets to cache the timestamp of the next record to be published.
	 * This value can be used to check if the cache is valid
	 * @see checkIfCacheIsValid()
	 * @uses Cake\I18n\Time::toUnixString()
	 * @uses $cache
	 */
	public function setNextToBePublished() {		
		$next = $this->find()
			->select('created')
			->where([
				sprintf('%s.active', $this->alias())	=> TRUE,
				sprintf('%s.created >', $this->alias()) => new Time()
			])
			->order([sprintf('%s.created', $this->alias()) => 'ASC'])
			->first();
		
		Cache::write('next_to_be_published', empty($next->created) ? FALSE : $next->created->toUnixString(), $this->cache);
	}

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\PostValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\PostValidator;
    }
}