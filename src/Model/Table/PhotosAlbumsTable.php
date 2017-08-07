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
        $rules->add($rules->isUnique(['slug'], I18N_VALUE_ALREADY_USED));
        $rules->add($rules->isUnique(['title'], I18N_VALUE_ALREADY_USED));

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
        $query->matching($this->Photos->getAlias(), function ($q) {
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

        $this->setTable('photos_albums');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->hasMany('Photos', ['className' => ME_CMS . '.Photos'])
            ->setForeignKey('album_id');

        $this->addBehavior('Timestamp');

        $this->_validatorClass = '\MeCms\Model\Validation\PhotosAlbumValidator';
    }
}
