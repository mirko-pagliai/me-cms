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

use ArrayObject;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * PhotosAlbums model
 */
class PhotosAlbumsTable extends AppTable
{
    /**
     * Name of the configuration to use for this table
     * @var string
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
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        //Deletes the directory
        if (file_exists($entity->path)) {
            //@codingStandardsIgnoreLine
            @rmdir($entity->path);
        }

        parent::afterDelete($event, $entity, $options);
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterSave()
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        //Creates the folder
        if (!file_exists($entity->path)) {
            (new Folder())->create($entity->path, 0777);
        }

        parent::afterSave($event, $entity, $options);
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['slug'], __d('me_cms', 'This value is already used')));
        $rules->add($rules->isUnique(['title'], __d('me_cms', 'This value is already used')));

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
            sprintf('%s.photo_count >', $this->alias()) => 0,
        ]);

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

        $this->table('photos_albums');
        $this->displayField('title');
        $this->primaryKey('id');

        $this->hasMany('Photos', [
            'foreignKey' => 'album_id',
            'className' => 'MeCms.Photos',
        ]);

        $this->addBehavior('Timestamp');

        $this->_validatorClass = '\MeCms\Model\Validation\PhotosAlbumValidator';
    }
}
