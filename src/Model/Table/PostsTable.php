<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Database\Schema\Table as Schema;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Entity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\Traits\GetPreviewFromTextTrait;
use MeCms\Model\Table\Traits\IsOwnedByTrait;
use MeCms\Model\Table\Traits\NextToBePublishedTrait;

/**
 * Posts model
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 * @method \MeCms\Model\Entity\Post get($primaryKey, $options = [])
 * @method \MeCms\Model\Entity\Post newEntity($data = null, array $options = [])
 * @method \MeCms\Model\Entity\Post[] newEntities(array $data, array $options = [])
 * @method \MeCms\Model\Entity\Post|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MeCms\Model\Entity\Post patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \MeCms\Model\Entity\Post[] patchEntities($entities, array $data, array $options = [])
 * @method \MeCms\Model\Entity\Post findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class PostsTable extends AppTable
{
    use GetPreviewFromTextTrait;
    use IsOwnedByTrait;
    use LocatorAwareTrait;
    use NextToBePublishedTrait;

    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'posts';

    /**
     * Alters the schema used by this table. This function is only called after
     *  fetching the schema out of the database
     * @param Cake\Database\Schema\TableSchema $schema TableSchema instance
     * @return Cake\Database\Schema\TableSchema TableSchema instance
     * @since 2.17.0
     */
    protected function _initializeSchema(Schema $schema)
    {
        $schema->setColumnType('preview', 'json');

        return $schema;
    }

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
     * @uses \MeCms\Model\Table\AppTable::getList()
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (!empty($data['tags_as_string'])) {
            //Gets existing tags
            $existingTags = $this->Tags->getList()->toArray();

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
     * Called before each entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.17.0
     * @uses MeCms\Model\Table\AppTable::beforeSave()
     * @uses MeCms\Model\Table\Traits\GetPreviewFromTextTrait::getPreview()
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        parent::beforeSave($event, $entity, $options);

        $entity->preview = $this->getPreview($entity->text);
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['category_id'], 'Categories', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->existsIn(['user_id'], 'Users', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED));
        $rules->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));

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
     * @return array Array of entities
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

        if (empty($related)) {
            $related = [];

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
                        ->select(['id', 'title', 'slug', 'text', 'preview'])
                        ->matching('Tags', function (Query $q) use ($tag) {
                            return $q->where([sprintf('%s.id', $this->Tags->getAlias()) => $tag->id]);
                        })
                        ->where([sprintf('%s.id NOT IN', $this->getAlias()) => $exclude]);

                    if ($images) {
                        $post->where([sprintf('%s.preview IS NOT', $this->getAlias()) => null]);
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

        $this->belongsTo('Categories', ['className' => ME_CMS . '.PostsCategories'])
            ->setForeignKey('category_id')
            ->setJoinType('INNER')
            ->setTarget($this->getTableLocator()->get(ME_CMS . '.PostsCategories'))
            ->setAlias('Categories');

        $this->belongsTo('Users', ['className' => ME_CMS . '.Users'])
            ->setForeignKey('user_id')
            ->setJoinType('INNER');

        $this->belongsToMany('Tags', ['className' => ME_CMS . '.Tags', 'joinTable' => 'posts_tags'])
            ->setForeignKey('post_id')
            ->setTargetForeignKey('tag_id')
            ->setThrough(ME_CMS . '.PostsTags');

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
            $query->matching('Tags', function (Query $q) use ($data) {
                return $q->where([sprintf('%s.tag', $this->Tags->getAlias()) => $data['tag']]);
            })->distinct();
        }

        return $query;
    }
}
