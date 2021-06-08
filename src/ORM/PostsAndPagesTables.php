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
 * @since       2.23.0
 */

namespace MeCms\ORM;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use MeCms\Model\Table\AppTable;
use MeCms\Model\Table\Traits\GetPreviewsFromTextTrait;
use MeCms\Model\Table\Traits\NextToBePublishedTrait;

/**
 * Abstract class for `PostsTable` and `PagesTable` table classes.
 *
 * This class provides some methods and properties common to both classes.
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @method findActiveBySlug(string $slug)
 * @method findPendingBySlug(string $slug)
 */
abstract class PostsAndPagesTables extends AppTable
{
    use GetPreviewsFromTextTrait;
    use NextToBePublishedTrait;

    /**
     * Alters the schema used by this table. This function is only called after
     *  fetching the schema out of the database
     * @param \Cake\Database\Schema\TableSchemaInterface $schema The table
     *  definition fetched from database
     * @return \Cake\Database\Schema\TableSchemaInterface The altered schema
     * @since 2.17.0
     */
    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        return $schema->setColumnType('preview', 'jsonEntity');
    }

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     * @uses \MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterDelete(Event $event, EntityInterface $entity): void
    {
        parent::afterDelete($event, $entity);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     * @uses \MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        parent::afterSave($event, $entity);

        //Sets the next record to be published
        $this->setNextToBePublished();
    }

    /**
     * Called before each entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     * @since 2.17.0
     * @uses \MeCms\Model\Table\Traits\GetPreviewFromTextTrait::getPreviews()
     */
    public function beforeSave(Event $event, EntityInterface $entity): void
    {
        $entity->set('preview', $this->getPreviews($entity->get('text')));
    }

    /**
     * Creates a new Query for this repository and applies some defaults based
     *  on the type of search that was selected
     * @param string $type The type of query to perform
     * @param array|\ArrayAccess $options An array that will be passed to
     *  Query::applyOptions()
     * @return \Cake\ORM\Query The query builder
     * @uses \MeCms\Model\Table\Traits\NextToBePublishedTrait::getNextToBePublished()
     * @uses \MeCms\Model\Table\Traits\NextToBePublishedTrait::setNextToBePublished()
     * @uses clearCache()
     */
    public function find($type = 'all', $options = []): Query
    {
        //Gets from cache the timestamp of the next record to be published
        $next = $this->getNextToBePublished();

        //If the cache is invalid, it clears the cache and sets the next record
        //  to be published
        if ($next && time() >= $next) {
            $this->clearCache();
            $this->setNextToBePublished();
        }

        return parent::find($type, $options);
    }
}
