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
use Cake\Event\Event;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\Traits\IsOwnedByTrait;
use MeCms\Model\Validation\PostValidator;
use MeCms\ORM\PostsAndPagesTables;

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
class PostsTable extends PostsAndPagesTables
{
    use IsOwnedByTrait, LocatorAwareTrait;

    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'posts';

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
        parent::beforeMarshal($event, $data, $options);

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
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        return $rules->add($rules->existsIn(['category_id'], 'Categories', I18N_SELECT_VALID_OPTION))
            ->add($rules->existsIn(['user_id'], 'Users', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED))
            ->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * `forIndex()` find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     * @since 2.22.8
     */
    public function findForIndex(Query $query, array $options)
    {
        return $query->contain([
                $this->Categories->getAlias() => ['fields' => ['title', 'slug']],
                $this->Tags->getAlias() => ['sort' => ['tag' => 'ASC']],
                $this->Users->getAlias() => ['fields' => ['id', 'first_name', 'last_name']],
            ])
            ->select(['id', 'title', 'preview', 'subtitle', 'slug', 'text', 'enable_comments', 'created'])
            ->order([sprintf('%s.created', $this->getAlias()) => 'DESC']);
    }

    /**
     * Gets the related posts for a post
     * @param \MeCms\Model\Entity\Post $post Post entity. It must contain `id` and `Tags`
     * @param int $limit Limit of related posts
     * @param bool $images If `true`, gets only posts with images
     * @return array Array of entities
     * @uses queryForRelated()
     * @uses $cache
     */
    public function getRelated(Post $post, $limit = 5, $images = true)
    {
        key_exists_or_fail(['id', 'tags'], $post->toArray(), __d('me_cms', 'ID or tags of the post are missing'));

        $cache = sprintf('related_%s_posts_for_%s', $limit, $post->id);
        $cache = $images ? $cache . '_with_images' : $cache;

        return Cache::remember($cache, function () use ($images, $limit, $post) {
            $related = [];

            if ($post->has('tags')) {
                //Sorts and takes tags by `post_count` field
                $tags = collection($post->tags)->sortBy('post_count')->take($limit)->toList();

                //This array will be contain the ID to be excluded
                $exclude[] = $post->id;

                //For each tag, gets a related post.
                //It reverses the tags order, because the tags less popular have
                //  less chance to find a related post
                foreach (array_reverse($tags) as $tag) {
                    $post = $this->queryForRelated($tag->id, $images)
                        ->where([sprintf('%s.id NOT IN', $this->getAlias()) => $exclude])
                        ->first();

                    //Adds the current post to the related posts and its ID to the
                    //  IDs to be excluded for the next query
                    if ($post) {
                        $related[] = $post;
                        $exclude[] = $post->id;
                    }
                }
            }

            return $related;
        }, $this->getCacheName());
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
            ->setJoinType('INNER')
            ->setTarget($this->getTableLocator()->get('MeCms.PostsCategories'))
            ->setAlias('Categories');

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

        $this->_validatorClass = PostValidator::class;
    }

    /**
     * Gets the query for related posts from a tag ID
     * @param int $tagId Tag ID
     * @param bool $onlyWithImages If `true`, gets only posts with images
     * @return \Cake\ORM\Query The query builder
     * @since 2.23.0
     */
    public function queryForRelated($tagId, $onlyWithImages = true)
    {
        $query = $this->find('active')
            ->select(['id', 'title', 'preview', 'slug', 'text'])
            ->matching('Tags', function (Query $q) use ($tagId) {
                return $q->where([sprintf('%s.id', $this->Tags->getAlias()) => $tagId]);
            });

        if ($onlyWithImages) {
            $query->where([sprintf('%s.preview NOT IN', $this->getAlias()) => [null, []]]);
        }

        return $query;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return \Cake\ORM\Query $query Query object
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
