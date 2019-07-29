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
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\BannerValidator;

/**
 * Banners model
 * @property \Cake\ORM\Association\BelongsTo $Positions
 */
class BannersTable extends AppTable
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache = 'banners';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterDelete()
     */
    public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        @unlink(BANNERS . $entity->get('filename'));

        parent::afterDelete($event, $entity, $options);
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        return $rules->add($rules->existsIn(['position_id'], 'Positions', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['filename'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        return $query->where([sprintf('%s.active', $this->getAlias()) => true]);
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('banners');
        $this->setDisplayField('filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Positions', ['className' => 'MeCms.BannersPositions'])
            ->setForeignKey('position_id')
            ->setJoinType('INNER');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Positions' => ['banner_count']]);

        $this->_validatorClass = BannerValidator::class;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data ($this->getRequest()->getQueryParams())
     * @return \Cake\ORM\Query $query Query object
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Position" field
        if (!empty($data['position']) && is_positive($data['position'])) {
            $query->where([sprintf('%s.position_id', $this->getAlias()) => $data['position']]);
        }

        return $query;
    }
}
