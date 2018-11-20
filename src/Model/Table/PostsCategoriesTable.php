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

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * PostsCategories model
 * @property \Cake\ORM\Association\BelongsTo $Parents
 * @property \Cake\ORM\Association\HasMany $Childs
 */
class PostsCategoriesTable extends AppTable
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
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'Parents', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED));
        $rules->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));

        return $rules;
    }

    /**
     * "active" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        $query->matching($this->Posts->getAlias(), function (Query $q) {
            return $q->find('active');
        })->distinct();

        return $query;
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('posts_categories');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Parents', ['className' => ME_CMS . '.PostsCategories'])
            ->setForeignKey('parent_id');

        $this->hasMany('Childs', ['className' => ME_CMS . '.PostsCategories'])
            ->setForeignKey('parent_id');

        $this->hasMany('Posts', ['className' => ME_CMS . '.Posts'])
            ->setForeignKey('category_id');

        $this->addBehavior('Timestamp');
        $this->addBehavior(ME_CMS . '.Tree');

        $this->_validatorClass = '\MeCms\Model\Validation\PostsCategoryValidator';
    }
}
