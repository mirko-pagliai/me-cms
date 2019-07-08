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

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\TagValidator;

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
        return $rules->add($rules->isUnique(['tag'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        return $query->innerJoinWith('Posts', function (Query $q) {
            return $q->find('active');
        })->distinct();
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

        $this->_validatorClass = TagValidator::class;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return \Cake\ORM\Query $query Query object
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
