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
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;

/**
 * Photos model
 * @property \Cake\ORM\Association\BelongsTo $Albums
 * @method \MeCms\Model\Entity\Photo get($primaryKey, $options = [])
 * @method \MeCms\Model\Entity\Photo newEntity($data = null, array $options = [])
 * @method \MeCms\Model\Entity\Photo[] newEntities(array $data, array $options = [])
 * @method \MeCms\Model\Entity\Photo|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MeCms\Model\Entity\Photo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \MeCms\Model\Entity\Photo[] patchEntities($entities, array $data, array $options = [])
 * @method \MeCms\Model\Entity\Photo findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class PhotosTable extends AppTable
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
        //Deletes the file
        if (file_exists($entity->path) && is_writable($entity->path)) {
            //@codingStandardsIgnoreLine
            @unlink($entity->path);
        }

        parent::afterDelete($event, $entity, $options);
    }

    /**
     * Called before each entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.17.0
     * @uses MeCms\Model\Table\AppTable::beforeSave()
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        parent::beforeSave($event, $entity, $options);

        list($width, $height) = getimagesize($entity->path);

        $entity->size = compact('width', 'height');
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['album_id'], 'Albums', I18N_SELECT_VALID_OPTION));
        $rules->add($rules->isUnique(['filename'], I18N_VALUE_ALREADY_USED));

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
        $query->where([sprintf('%s.active', $this->getAlias()) => true]);

        return $query;
    }

    /**
     * "pending" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findPending(Query $query, array $options)
    {
        $query->where([sprintf('%s.active', $this->getAlias()) => false]);

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

        $this->setTable('photos');
        $this->setDisplayField('filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Albums', ['className' => ME_CMS . '.PhotosAlbums'])
            ->setForeignKey('album_id')
            ->setJoinType('INNER');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Albums' => ['photo_count']]);

        $this->_validatorClass = '\MeCms\Model\Validation\PhotoValidator';
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return Query $query Query object
     * @uses \MeCms\Model\Table\AppTable::queryFromFilter()
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        $query = parent::queryFromFilter($query, $data);

        //"Album" field
        if (!empty($data['album']) && is_positive($data['album'])) {
            $query->where([sprintf('%s.album_id', $this->getAlias()) => $data['album']]);
        }

        return $query;
    }
}
