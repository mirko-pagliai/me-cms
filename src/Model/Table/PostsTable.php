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
namespace MeCms\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\Traits\NextToBePublishedTrait;

/**
 * Posts model
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 */
class PostsTable extends AppTable
{
    use NextToBePublishedTrait;

    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'posts';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterDelete()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        parent::afterDelete($event, $entity, $options);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterSave()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        parent::afterSave($event, $entity, $options);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called before request data is converted into entities
     * @param \Cake\Event\Event $event Event object
     * @param \ArrayObject $data Request data
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.15.2
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (!empty($data['tags_as_string'])) {
            //Gets existing tags
            $existingTags = $this->Tags->getList();

            $tags = array_unique(preg_split('/\s*,+\s*/', $data['tags_as_string']));

            //For each tag, it searches if the tag already exists.
            //If a tag exists in the database, it sets also the tag ID
            foreach ($tags as $k => $tag) {
                $id = array_search($tag, $existingTags);

                if ($id) {
                    $data['tags'][$k]['id'] = $id;
                }

                $data['tags'][$k]['tag'] = $tag;
            }
        }
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['category_id'], 'Categories', __d('me_cms', 'You have to select a valid option')));
        $rules->add($rules->existsIn(['user_id'], 'Users', __d('me_cms', 'You have to select a valid option')));
        $rules->add($rules->isUnique(['slug'], __d('me_cms', 'This value is already used')));
        $rules->add($rules->isUnique(['title'], __d('me_cms', 'This value is already used')));

        return $rules;
    }

    /**
     * Creates a new Query for this repository and applies some defaults based
     *  on the type of search that was selected
     * @param string $type The type of query to perform
     * @param array|ArrayAccess $options An array that will be passed to
     *  Query::applyOptions()
     * @return \Cake\ORM\Query The query builder
     * @uses $cache
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::getNextToBePublished()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function find($type = 'all', $options = [])
    {
        //Gets from cache the timestamp of the next record to be published
        $next = $this->getNextToBePublished();

        //If the cache is invalid, it clears the cache and sets the next record
        //  to be published
        if ($next && time() >= $next) {
            Cache::clear(false, $this->cache);

            //Sets the next record to be published
            $this->setNextToBePublished();
        }

        return parent::find($type, $options);
    }

    /**
     * Gets the related posts for a post
     * @param \MeCms\Model\Entity\Post $post Post entity. It must contain `id` and `Tags`
     * @param int $limit Limit of related posts
     * @param bool $images If true, gets only posts with images
     * @return array|null Related posts, array of entities
     * @throws InternalErrorException
     * @uses $cache
     */
    public function getRelated(Post $post, $limit = 5, $images = true)
    {
        if (empty($post->id) || !isset($post->tags)) {
            throw new InternalErrorException(__d('me_cms', 'ID or tags of the post are missing'));
        }

        $cache = sprintf('related_%s_posts_for_%s', $limit, $post->id);

        if ($images) {
            $cache .= '_with_images';
        }

        //Tries to gets related posts from cache.
        //A `null` value means that there are no related post
        $related = Cache::read($cache, $this->cache);

        if (empty($related) && !is_null($related)) {
            if (!empty($post->tags)) {
                //Sorts and takes tags by `post_count` field
                $tags = collection($post->tags)->sortBy('post_count')->take($limit)->toList();

                //This array will be contain the ID to be excluded
                $exclude[] = $post->id;

                //For each tag, gets a related post.
                //It reverses the tags order, because the tags less popular have
                //  less chance to find a related post
                foreach (array_reverse($tags) as $tag) {
                    $post = $this->find('active')
                        ->select(['id', 'title', 'slug', 'text'])
                        ->matching('Tags', function ($q) use ($tag) {
                            return $q->where([sprintf('%s.id', $this->Tags->getAlias()) => $tag->id]);
                        })
                        ->where([sprintf('%s.id NOT IN', $this->getAlias()) => $exclude]);

                    if ($images) {
                        $post->where([sprintf('%s.text LIKE', $this->getAlias()) => sprintf('%%%s%%', '<img')]);
                    }

                    $post = $post->first();

                    //Adds the current post to the related posts and its ID to the
                    //  IDs to be excluded for the next query
                    if (!empty($post)) {
                        $related[] = $post;
                        $exclude[] = $post->id;
                    }
                }
            }

            if (empty($related)) {
                $related = null;
            }

            Cache::write($cache, $related, $this->cache);
        }

        return $related;
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('posts');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Categories', ['className' => 'MeCms.PostsCategories'])
            ->setForeignKey('category_id')
            ->setJoinType('INNER');

        $this->belongsTo('Users', ['className' => 'MeCms.Users'])
            ->setForeignKey('user_id')
            ->setJoinType('INNER');

        $this->belongsToMany('Tags', ['className' => 'MeCms.Tags', 'joinTable' => 'posts_tags'])
            ->setForeignKey('post_id')
            ->setTargetForeignKey('tag_id')
            ->setThrough('MeCms.PostsTags');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'Categories' => ['post_count'],
            'Users' => ['post_count'],
        ]);

        $this->_validatorClass = '\MeCms\Model\Validation\PostValidator';
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->getQuery())
     * @return Query $query Query object
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Tag" field
        if (!empty($data['tag']) && strlen($data['tag']) > 2) {
            $query->matching('Tags', function ($q) use ($data) {
                return $q->where([sprintf('%s.tag', $this->Tags->getAlias()) => $data['tag']]);
            });
        }

        return $query;
    }
}
