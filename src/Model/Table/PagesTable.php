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
use MeCms\Model\Entity\Page;
use MeCms\Model\Table\AppTable;
use Cake\Cache\Cache;

/**
 * Pages model
 */
class PagesTable extends AppTable {
	/**
	 * Name of the configuration to use for this table
	 * @var string|array
	 */
	public $cache = 'pages';
	
    /**
     * Initialize method
     * @param array $config The configuration for the table
     */
    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('pages');
        $this->displayField('title');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
    
    /**
     * Creates a new Query for this repository and applies some defaults based on the type of search that was selected
     * @param string $type The type of query to perform
     * @param array|ArrayAccess $options An array that will be passed to Query::applyOptions()
     * @return Cake\ORM\Query The query builder
     * @uses setNextToBePublished()
	 * @uses $cache
     */
    public function find($type = 'all', $options = []) {
        //Gets from cache the timestamp of the next record to be published
		$next = Cache::read('next_to_be_published', $this->cache);
		
		//If the cache is not valid, it empties the cache
		if($next && time() >= $next) {
			Cache::clear(FALSE, $this->cache);
		
			//Sets the next record to be published
			$this->setNextToBePublished();
		}
        
        return parent::find($type, $options);
    }
	
	/**
	 * Sets to cache the timestamp of the next record to be published.
	 * This value can be used to check if the cache is valid
	 * @uses $cache
	 */
	public function setNextToBePublished() {		
		$next = $this->find()
			->select('created')
			->where([
				sprintf('%s.active', $this->alias()) => TRUE,
				sprintf('%s.created >', $this->alias()) => new Time(),
			])
			->order([sprintf('%s.created', $this->alias()) => 'ASC'])
			->first();
        
        $next = empty($next->created) ? FALSE : $next->created->toUnixString();
		
		Cache::write('next_to_be_published', $next, $this->cache);
	}

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
	 * @return \MeCms\Model\Validation\PageValidator
	 */
    public function validationDefault(\Cake\Validation\Validator $validator) {
		return new \MeCms\Model\Validation\PageValidator;
    }
}