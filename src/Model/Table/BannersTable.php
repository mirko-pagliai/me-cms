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
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * Banners model
 * @property \Cake\ORM\Association\BelongsTo $Positions
 */
class BannersTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string
     */
    public $cache = 'banners';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterDelete()
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        //Deletes the file
        (new File(BANNERS . $entity->filename))->delete();

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
        $rules->add($rules->existsIn(['position_id'], 'Positions', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->isUnique(['filename'], I18N_VALUE_ALREADY_USED));

        return $rules;
    }

    /**
     * "Active" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        $query->where([sprintf('%s.active', $this->getAlias()) => true]);

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

        $this->setTable('banners');
        $this->setDisplayField('filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Positions', ['className' => ME_CMS . '.BannersPositions'])
            ->setForeignKey('position_id')
            ->setJoinType('INNER');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Positions' => ['banner_count']]);

        $this->_validatorClass = '\MeCms\Model\Validation\BannerValidator';
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

        //"Position" field
        if (!empty($data['position']) && isPositive($data['position'])) {
            $query->where([sprintf('%s.position_id', $this->getAlias()) => $data['position']]);
        }

        return $query;
    }
}
