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

use Cake\ORM\Query as CakeQuery;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\PostsTable;
use MeCms\Model\Table\PostsTagsTable;
use MeCms\Model\Validation\TagValidator;
use MeCms\ORM\Query;

/**
 * Tags model
 * @property \Cake\ORM\Association\BelongsToMany $Posts
 * @method findActiveByTag(string $tag)
 */
class TagsTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'posts';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->isUnique(['tag'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findActive(Query $query): Query
    {
        return $query->innerJoinWith('Posts', function (Query $query) {
            return $query->find('active');
        })->distinct();
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setDisplayField('tag');
        $this->setPrimaryKey('id');

        $this->belongsToMany('Posts', ['className' => PostsTable::class, 'joinTable' => 'posts_tags'])
            ->setForeignKey('tag_id')
            ->setTargetForeignKey('post_id')
            ->setThrough(PostsTagsTable::class);

        $this->addBehavior('Timestamp');

        $this->_validatorClass = TagValidator::class;
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

        //"Name" field
        if (!empty($data['name']) && strlen($data['name']) > 2) {
            $query->where(['tag LIKE' => '%' . $data['name'] . '%']);
        }

        return $query;
    }
}
