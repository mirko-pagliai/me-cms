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
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\PagesCategoryValidator;
use MeCms\ORM\Query;

/**
 * PagesCategories model
 * @property \Cake\ORM\Association\BelongsTo $ParentPagesCategories
 * @property \Cake\ORM\Association\HasMany $ChildPagesCategories
 */
class PagesCategoriesTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'pages';

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->existsIn(['parent_id'], 'Parents', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED))
            ->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findActive(Query $query): Query
    {
        return $query->innerJoinWith($this->Pages->getAlias(), function (Query $query) {
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

        $this->setAlias('Categories');
        $this->setTable('pages_categories');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Parents', ['className' => 'MeCms.PagesCategories'])
            ->setForeignKey('parent_id');

        $this->hasMany('Childs', ['className' => 'MeCms.PagesCategories'])
            ->setForeignKey('parent_id');

        $this->hasMany('Pages', ['className' => 'MeCms.Pages'])
            ->setForeignKey('category_id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('MeCms.Tree');

        $this->_validatorClass = PagesCategoryValidator::class;
    }
}
