<?php
declare(strict_types=1);

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
use Cake\Collection\CollectionInterface;
use Cake\Event\Event;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query as CakeQuery;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Post;
use MeCms\Model\Table\PostsCategoriesTable;
use MeCms\Model\Table\PostsTagsTable;
use MeCms\Model\Table\TagsTable;
use MeCms\Model\Table\Traits\IsOwnedByTrait;
use MeCms\Model\Table\UsersTable;
use MeCms\Model\Validation\PostValidator;
use MeCms\ORM\PostsAndPagesTables;
use MeCms\ORM\Query;
use Tools\Exceptionist;

/**
 * Posts model
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany&\MeCms\Model\Table\TagsTable $Tags
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
    use IsOwnedByTrait;
    use LocatorAwareTrait;

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
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options): void
    {
        parent::beforeMarshal($event, $data, $options);

        if (!empty($data['tags_as_string'])) {
            $tags = array_unique(preg_split('/\s*,+\s*/', $data['tags_as_string']) ?: []);

            //Gets existing tags
            $existingTags = $this->Tags->getList()->toArray();

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
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->existsIn(['category_id'], 'Categories', I18N_SELECT_VALID_OPTION))
            ->add($rules->existsIn(['user_id'], 'Users', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED))
            ->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * `forIndex()` find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     * @since 2.22.8
     */
    public function findForIndex(Query $query): Query
    {
        return $query->contain([
            $this->Categories->getAlias() => ['fields' => ['id', 'title', 'slug']],
            $this->Tags->getAlias() => ['sort' => ['tag' => 'ASC']],
            $this->Users->getAlias() => ['fields' => ['id', 'first_name', 'last_name']],
        ])
        ->orderDesc(sprintf('%s.created', $this->getAlias()));
    }

    /**
     * Gets the related posts for a post
     * @param \MeCms\Model\Entity\Post $post Post entity. It must contain `id` and `Tags`
     * @param int $limit Limit of related posts
     * @param bool $images If `true`, gets only posts with images
     * @return \Cake\Collection\CollectionInterface Collection of entities
     * @throws \Tools\Exception\PropertyNotExistsException
     * @uses queryForRelated()
     * @uses $cache
     */
    public function getRelated(Post $post, int $limit = 5, bool $images = true): CollectionInterface
    {
        Exceptionist::objectPropertyExists($post, ['id', 'tags']);

        $cache = sprintf('related_%s_posts_for_%s', $limit, $post->get('id'));
        $cache .= $images ? '_with_images' : '';

        return Cache::remember($cache, function () use ($images, $limit, $post) {
            $related = [];

            if ($post->has('tags')) {
                //Sorts and takes tags by `post_count` field
                $tags = collection($post->get('tags'))->sortBy('post_count')->take($limit)->toList();

                //This array will be contain the ID to be excluded
                $exclude[] = $post->get('id');

                //For each tag, gets a related post.
                //It reverses the tags order, because the tags less popular have
                //  less chance to find a related post
                foreach (array_reverse($tags) as $tag) {
                    /** @var \MeCms\Model\Entity\Post $post */
                    $post = $this->queryForRelated($tag->get('id'), $images)
                        ->where([sprintf('%s.id NOT IN', $this->getAlias()) => $exclude])
                        ->first();

                    //Adds the current post to the related posts and its ID to the
                    //  IDs to be excluded for the next query
                    if ($post) {
                        $related[] = $post;
                        $exclude[] = $post->get('id');
                    }
                }
            }

            return collection($related);
        }, $this->getCacheName());
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('posts');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Categories', ['className' => PostsCategoriesTable::class])
            ->setForeignKey('category_id')
            ->setJoinType('INNER')
            ->setTarget($this->getTableLocator()->get('MeCms.PostsCategories'))
            ->setAlias('Categories');

        $this->belongsTo('Users', ['className' => UsersTable::class])
            ->setForeignKey('user_id')
            ->setJoinType('INNER');

        $this->belongsToMany('Tags', ['className' => TagsTable::class, 'joinTable' => 'posts_tags'])
            ->setForeignKey('post_id')
            ->setTargetForeignKey('tag_id')
            ->setThrough(PostsTagsTable::class);

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
     * @return \Cake\ORM\Query $query Query object
     * @since 2.23.0
     */
    public function queryForRelated(int $tagId, bool $onlyWithImages = true): CakeQuery
    {
        $query = $this->find('active')
            ->select(['id', 'title', 'preview', 'slug', 'text'])
            ->innerJoinWith('Tags', function (Query $query) use ($tagId) {
                return $query->where([sprintf('%s.id', $this->Tags->getAlias()) => $tagId]);
            })->distinct();

        if ($onlyWithImages) {
            $query->where([sprintf('%s.preview IS NOT', $this->getAlias()) => null])
                ->andWhere([sprintf('%s.preview IS NOT', $this->getAlias()) => []]);
        }

        return $query;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data (`$this->getRequest()->getQueryParams()`)
     * @return \Cake\ORM\Query $query Query object
     */
    public function queryFromFilter(CakeQuery $query, array $data = []): CakeQuery
    {
        $query = parent::queryFromFilter($query, $data);

        //"Tag" field
        $tag = !empty($data['tag']) && strlen($data['tag']) > 2 ? $data['tag'] : false;
        if ($tag) {
            $query->innerJoinWith($this->Tags->getAlias(), function (Query $query) use ($tag) {
                return $query->where(compact('tag'));
            });
        }

        return $query;
    }
}
