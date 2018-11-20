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
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 * Application table class
 */
class AppTable extends Table
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache;

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses getCacheName()
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        if ($this->getCacheName()) {
            Cache::clear(false, $this->getCacheName());
        }
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses getCacheName()
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if ($this->getCacheName()) {
            Cache::clear(false, $this->getCacheName());
        }
    }

    /**
     * Called before each entity is saved. Stopping this event will abort the
     *  save operation. When the event is stopped the result of the event will
     *  be returned
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface object
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.16.1
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (empty($entity->created)) {
            $entity->created = new Time;
        } elseif (!empty($entity->created) && !$entity->created instanceof Time) {
            $entity->created = new Time($entity->created);
        }
    }

    /**
     * "active" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findActive(Query $query, array $options)
    {
        $query->where([sprintf('%s.active', $this->getAlias()) => true])
            ->where([sprintf('%s.created <=', $this->getAlias()) => new Time]);

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
        $query->where(['OR' => [
            sprintf('%s.active', $this->getAlias()) => false,
            sprintf('%s.created >', $this->getAlias()) => new Time,
        ]]);

        return $query;
    }

    /**
     * "random" find method
     * @param Query $query Query object
     * @param array $options Options
     * @return Query Query object
     */
    public function findRandom(Query $query, array $options)
    {
        $query->order('rand()');

        if (!$query->clause('limit')) {
            $query->limit(1);
        }

        return $query;
    }

    /**
     * Gets the cache configuration name used by this table
     * @param bool $associations If `true`, it returns an array that contains
     *  also the names of the associated tables
     * @return string|array|null
     * @since 2.26.0
     * @uses $cache
     */
    public function getCacheName($associations = false)
    {
        $values = $this->cache ?: null;

        if ($associations) {
            $values = [$values];
            foreach ($this->associations()->getIterator() as $association) {
                if (method_exists($association->getTarget(), 'getCacheName') && $association->getTarget()->getCacheName()) {
                    $values[] = $association->getTarget()->getCacheName();
                }
            }

            $values = array_values(array_unique(array_filter($values)));
        }

        return $values;
    }

    /**
     * Gets records as list
     * @return Query $query Query object
     * @uses getCacheName()
     */
    public function getList()
    {
        return $this->find('list')
            ->order([$this->getDisplayField() => 'ASC'])
            ->cache(sprintf('%s_list', $this->getTable()), $this->getCacheName());
    }

    /**
     * Gets records as tree list
     * @return Query $query Query object
     * @uses getCacheName()
     */
    public function getTreeList()
    {
        return $this->find('treeList')
            ->cache(sprintf('%s_tree_list', $this->getTable()), $this->getCacheName());
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return Query $query Query object
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        //"ID" field
        if (!empty($data['id']) && is_positive($data['id'])) {
            $query->where([sprintf('%s.id', $this->getAlias()) => $data['id']]);
        }

        //"Title" field
        if (!empty($data['title']) && strlen($data['title']) > 2) {
            $query->where([sprintf('%s.title LIKE', $this->getAlias()) => sprintf('%%%s%%', $data['title'])]);
        }

        //"Filename" field
        if (!empty($data['filename']) && strlen($data['filename']) > 2) {
            $query->where([sprintf('%s.filename LIKE', $this->getAlias()) => sprintf('%%%s%%', $data['filename'])]);
        }

        //"User" (author) field
        if (!empty($data['user']) && is_positive($data['user'])) {
            $query->where([sprintf('%s.user_id', $this->getAlias()) => $data['user']]);
        }

        //"Category" field
        if (!empty($data['category']) && is_positive($data['category'])) {
            $query->where([sprintf('%s.category_id', $this->getAlias()) => $data['category']]);
        }

        //"Active" field
        if (!empty($data['active'])) {
            $query->where([sprintf('%s.active', $this->getAlias()) => $data['active'] === 'yes']);
        }

        //"Priority" field
        if (!empty($data['priority']) && preg_match('/^[1-5]$/', $data['priority'])) {
            $query->where([sprintf('%s.priority', $this->getAlias()) => $data['priority']]);
        }

        //"Created" field
        if (!empty($data['created']) && preg_match('/^[1-9][0-9]{3}\-[0-1][0-9]$/', $data['created'])) {
            $start = new Time(sprintf('%s-01', $data['created']));
            $end = (new Time($start))->addMonth(1);

            $query->where([
                sprintf('%s.created >=', $this->getAlias()) => $start,
                sprintf('%s.created <', $this->getAlias()) => $end,
            ]);
        }

        return $query;
    }
}
