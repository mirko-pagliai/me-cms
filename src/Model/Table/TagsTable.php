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
 * Tags model
 * @property \Cake\ORM\Association\BelongsToMany $Posts
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
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['tag'], I18N_VALUE_ALREADY_USED));

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
        $query->innerJoinWith('Posts', function (Query $q) {
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

        $this->setTable('tags');
        $this->setDisplayField('tag');
        $this->setPrimaryKey('id');

        $this->belongsToMany('Posts', ['className' => 'MeCms.Posts', 'joinTable' => 'posts_tags'])
            ->setForeignKey('tag_id')
            ->setTargetForeignKey('post_id')
            ->setThrough('MeCms.PostsTags');

        $this->addBehavior('Timestamp');

        $this->_validatorClass = '\MeCms\Model\Validation\TagValidator';
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return Query $query Query object
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Name" field
        if (!empty($data['name']) && strlen($data['name']) > 2) {
            $query->where([sprintf('%s.tag LIKE', $this->getAlias()) => sprintf('%%%s%%', $data['name'])]);
        }

        return $query;
    }
}
