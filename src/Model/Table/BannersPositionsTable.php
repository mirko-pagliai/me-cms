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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\BannersPosition;
use MeCms\Model\Table\AppTable;

/**
 * BannersPositions model
 */
class BannersPositionsTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string|array
     */
    public $cache = 'banners';

    /**
     * Gets the positions list
     * @return array List
     * @uses $cache
     */
    public function getList()
    {
        return $this->find('list')
            ->order(['name' => 'ASC'])
            ->cache('positions_list', $this->cache)
            ->toArray();
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('banners_positions');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->hasMany('Banners', [
            'foreignKey' => 'position_id',
            'className' => 'MeCms.Banners',
        ]);

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\BannersPositionValidator
     */
    public function validationDefault(\Cake\Validation\Validator $validator)
    {
        return new \MeCms\Model\Validation\BannersPositionValidator;
    }
}
