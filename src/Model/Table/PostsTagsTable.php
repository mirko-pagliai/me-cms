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

use Cake\ORM\RulesChecker;
use MeCms\Model\Validation\PostsTagValidator;

/**
 * PostsTags model
 * @property \MeCms\Model\Table\TagsTable&\Cake\ORM\Association\BelongsTo $Tags
 * @property \Cake\ORM\Association\BelongsTo $Posts
 */
class PostsTagsTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected string $cache = 'posts';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->existsIn(['tag_id'], 'Tags', I18N_SELECT_VALID_OPTION))
            ->add($rules->existsIn(['post_id'], 'Posts', I18N_SELECT_VALID_OPTION));
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('posts_tags');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Posts', ['className' => PostsTable::class])
            ->setForeignKey('post_id')
            ->setJoinType('INNER');

        $this->belongsTo('Tags', ['className' => TagsTable::class])
            ->setForeignKey('tag_id')
            ->setJoinType('INNER');

        $this->addBehavior('CounterCache', ['Tags' => ['post_count']]);

        $this->_validatorClass = PostsTagValidator::class;
    }
}
