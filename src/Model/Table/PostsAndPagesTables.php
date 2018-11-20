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
 * @since       2.23.0
 */
namespace MeCms\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Entity;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\Traits\GetPreviewsFromTextTrait;
use MeCms\Model\Table\Traits\NextToBePublishedTrait;

/**
 * Abstract class for `PostsTable` and `PagesTable` classes.
 *
 * This class provides some methods common to both classes.
 */
abstract class PostsAndPagesTables extends AppTable
{
    use GetPreviewsFromTextTrait;
    use NextToBePublishedTrait;

    /**
     * Alters the schema used by this table. This function is only called after
     *  fetching the schema out of the database
     * @param Cake\Database\Schema\TableSchema $schema TableSchema instance
     * @return Cake\Database\Schema\TableSchema TableSchema instance
     * @since 2.17.0
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->setColumnType('preview', 'jsonEntity');

        return $schema;
    }

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterDelete()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        parent::afterDelete($event, $entity, $options);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses MeCms\Model\Table\AppTable::afterSave()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        parent::afterSave($event, $entity, $options);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called before each entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.17.0
     * @uses MeCms\Model\Table\AppTable::beforeSave()
     * @uses MeCms\Model\Table\Traits\GetPreviewFromTextTrait::getPreviews()
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        parent::beforeSave($event, $entity, $options);

        $entity->preview = $this->getPreviews($entity->text);
    }

    /**
     * Creates a new Query for this repository and applies some defaults based
     *  on the type of search that was selected
     * @param string $type The type of query to perform
     * @param array|ArrayAccess $options An array that will be passed to
     *  Query::applyOptions()
     * @return \Cake\ORM\Query The query builder
     * @uses getCacheName()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::getNextToBePublished()
     * @uses MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function find($type = 'all', $options = [])
    {
        //Gets from cache the timestamp of the next record to be published
        $next = $this->getNextToBePublished();

        //If the cache is invalid, it clears the cache and sets the next record
        //  to be published
        if ($next && time() >= $next) {
            Cache::clear(false, $this->getCacheName());

            //Sets the next record to be published
            $this->setNextToBePublished();
        }

        return parent::find($type, $options);
    }
}
