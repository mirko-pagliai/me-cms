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

use Cake\Filesystem\File;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Entity\Photo;
use MeCms\Model\Table\AppTable;

/**
 * Photos model
 * @property \Cake\ORM\Association\BelongsTo $Albums
 */
class PhotosTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string|array
     */
    public $cache = 'photos';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterDelete()
     */
    public function afterDelete(
        \Cake\Event\Event $event,
        \Cake\ORM\Entity $entity,
        \ArrayObject $options
    ) {
        //Deletes the file
        (new File(PHOTOS . DS . $entity->album_id . DS . $entity->filename))->delete();

        parent::afterDelete($event, $entity, $options);
    }

    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['album_id'], 'Albums'));

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
        $query->where([
            sprintf('%s.active', $this->alias()) => true,
        ]);
        $query->matching('Albums', function ($q) {
            return $q->where([
                sprintf('%s.active', $this->Albums->alias()) => true,
            ]);
        });

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

        $this->table('photos');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Albums', [
            'foreignKey' => 'album_id',
            'joinType' => 'INNER',
            'className' => 'MeCms.PhotosAlbums',
        ]);

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Albums' => ['photo_count']]);
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->query)
     * @return Query $query Query object
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Album" field
        if (!empty($data['album']) && preg_match('/^[1-9]\d*$/', $data['album'])) {
            $query->where([
                sprintf('%s.album_id', $this->alias()) => $data['album'],
            ]);
        }

        return $query;
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \MeCms\Model\Validation\PhotoValidator
     */
    public function validationDefault(\Cake\Validation\Validator $validator)
    {
        return new \MeCms\Model\Validation\PhotoValidator;
    }
}
