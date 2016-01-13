<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Banner;
use MeCms\Model\Table\AppTable;
use MeCms\Utility\BannerFile;

/**
 * Banners model
 * @property \Cake\ORM\Association\BelongsTo $Positions
 */
class BannersTable extends AppTable {
	/**
	 * Name of the configuration to use for this table
	 * @var string|array
	 */
	public $cache = 'banners';
	
	/**
	 * Called after an entity has been deleted
	 * @param \Cake\Event\Event $event Event object
	 * @param \Cake\ORM\Entity $entity Entity object
	 * @param \ArrayObject $options Options
	 * @uses MeCms\Model\Table\AppTable::afterDelete()
	 * @uses MeCms\Utility\BannerFile::delete()
	 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, \ArrayObject $options) {
		//Deletes the file
		BannerFile::delete($entity->filename);
		
		parent::afterDelete($event, $entity, $options);
	}

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->existsIn(['position_id'], 'Positions'));
        return $rules;
    }
	
	/**
	 * "Active" find method
	 * @param Query $query Query object
	 * @param array $options Options
	 * @return Query Query object
	 */
	public function findActive(Query $query, array $options) {		
        $query->where([sprintf('%s.active', $this->alias()) => TRUE]);
		
        return $query;
    }
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('banners');
        $this->displayField('id');
        $this->primaryKey('id');
		
        $this->belongsTo('Positions', [
            'foreignKey' => 'position_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.BannersPositions'
        ]);
		
        $this->addBehavior('CounterCache', ['Positions' => ['banner_count']]);
    }
	
	/**
	 * Build query from filter data
	 * @param Query $query Query object
	 * @param array $data Filter data ($this->request->query)
	 * @return Query $query Query object
	 * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
	 */
	public function queryFromFilter(Query $query, array $data = []) {
		$query = parent::queryFromFilter($query, $data);
		
		//"Position" field
		if(!empty($data['position']) && preg_match('/^[1-9]\d*$/', $data['position']))
			$query->where([sprintf('%s.position_id', $this->alias()) => $data['position']]);
		
		return $query;
	}

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\BannerValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\BannerValidator;
    }
}