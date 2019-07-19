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

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Validation\PhotoValidator;

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
     * Cache configuration name
     * @var string
     */
    protected $cache = 'photos';

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     */
    public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        @unlink($entity->get('path'));

        parent::afterDelete($event, $entity, $options);
    }

    /**
     * Called before each entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.17.0
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        [$width, $height] = getimagesize($entity->get('path'));
        $entity->set('size', compact('width', 'height'));
    }

    /**
     * Returns a rules checker object that will be used for validating
     *  application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add($rules->existsIn(['album_id'], 'Albums', I18N_SELECT_VALID_OPTION))
            ->add($rules->isUnique(['filename'], I18N_VALUE_ALREADY_USED));
    }

    /**
     * "active" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findActive(Query $query, array $options): Query
    {
        return $query->where([sprintf('%s.active', $this->getAlias()) => true]);
    }

    /**
     * "pending" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findPending(Query $query, array $options): Query
    {
        return $query->where([sprintf('%s.active', $this->getAlias()) => false]);
    }

    /**
     * Initialize method
     * @param array $config The configuration for the table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('photos');
        $this->setDisplayField('filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Albums', ['className' => 'MeCms.PhotosAlbums'])
            ->setForeignKey('album_id')
            ->setJoinType('INNER');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', ['Albums' => ['photo_count']]);

        $this->_validatorClass = PhotoValidator::class;
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data ($this->getRequest()->getQueryParams())
     * @return \Cake\ORM\Query $query Query object
     */
    public function queryFromFilter(Query $query, array $data = []): Query
    {
        $query = parent::queryFromFilter($query, $data);

        //"Album" field
        if (!empty($data['album']) && is_positive($data['album'])) {
            $query->where([sprintf('%s.album_id', $this->getAlias()) => $data['album']]);
        }

        return $query;
    }
}
