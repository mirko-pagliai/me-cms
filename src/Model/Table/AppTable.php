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
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses $cache
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!empty($this->cache)) {
            Cache::clear(false, $this->cache);
        }
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\ORM\Entity $entity Entity object
     * @param \ArrayObject $options Options
     * @return void
     * @uses $cache
     */
    public function afterSave(Event $event, Entity $entity, ArrayObject $options)
    {
        if (!empty($this->cache)) {
            Cache::clear(false, $this->cache);
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
        if (array_key_exists('created', $entity->toArray()) && !$entity->created instanceof Time) {
            $entity->created = new Time($entity->created);
        }
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
            sprintf('%s.created <=', $this->alias()) => new Time,
        ]);

        return $query;
    }

    /**
     * "Random" find method
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
     * Gets from cache the timestamp of the next record to be published.
     * This value can be used to check if the cache is valid
     * @return string|bool Timestamp or `false`
     */
    public function getNextToBePublished()
    {
        return Cache::read('next_to_be_published', $this->cache);
    }

    /**
     * Gets the categories list
     * @return array
     * @uses $cache
     */
    public function getList()
    {
        return $this->find('list')
            ->order([$this->displayField() => 'ASC'])
            ->cache(sprintf('%s_list', $this->table()), $this->cache)
            ->toArray();
    }

    /**
     * Gets the categories tree list
     * @return array
     * @uses $cache
     */
    public function getTreeList()
    {
        return $this->find('treeList')
            ->cache(sprintf('%s_tree_list', $this->table()), $this->cache)
            ->toArray();
    }

    /**
     * Checks whether a record belongs to an user.
     *
     * For example:
     * <code>
     * $posts = TableRegistry::get('Posts');
     * $posts->isOwnedBy(2, 4);
     * </code>
     * checks if the posts with ID 2 belongs to the user with ID 4.
     * @param int $id Record ID
     * @param int $userId User ID
     * @return bool
     */
    public function isOwnedBy($id, $userId = null)
    {
        return (bool)$this->find()
            ->where(am(compact('id'), ['user_id' => $userId]))
            ->first();
    }

    /**
     * Build query from filter data
     * @param Query $query Query object
     * @param array $data Filter data ($this->request->query)
     * @return Query $query Query object
     */
    public function queryFromFilter(Query $query, array $data = [])
    {
        //"ID" field
        if (!empty($data['id']) && isPositive($data['id'])) {
            $query->where([sprintf('%s.id', $this->alias()) => $data['id']]);
        }

        //"Title" field
        if (!empty($data['title']) && strlen($data['title']) > 2) {
            $query->where([sprintf('%s.title LIKE', $this->alias()) => sprintf('%%%s%%', $data['title'])]);
        }

        //"Filename" field
        if (!empty($data['filename']) && strlen($data['filename']) > 2) {
            $query->where([sprintf('%s.filename LIKE', $this->alias()) => sprintf('%%%s%%', $data['filename'])]);
        }

        //"User" (author) field
        if (!empty($data['user']) && isPositive($data['user'])) {
            $query->where([sprintf('%s.user_id', $this->alias()) => $data['user']]);
        }

        //"Category" field
        if (!empty($data['category']) && isPositive($data['category'])) {
            $query->where([sprintf('%s.category_id', $this->alias()) => $data['category']]);
        }

        //"Active" field
        if (!empty($data['active'])) {
            $query->where([sprintf('%s.active', $this->alias()) => $data['active'] === 'yes']);
        }

        //"Priority" field
        if (!empty($data['priority']) && preg_match('/^[1-5]$/', $data['priority'])) {
            $query->where([sprintf('%s.priority', $this->alias()) => $data['priority']]);
        }

        //"Created" field
        if (!empty($data['created']) && preg_match('/^[1-9][0-9]{3}\-[0-1][0-9]$/', $data['created'])) {
            $start = new Time(sprintf('%s-01', $data['created']));
            $end = (new Time($start))->addMonth(1);

            $query->where([
                sprintf('%s.created >=', $this->alias()) => $start,
                sprintf('%s.created <', $this->alias()) => $end,
            ]);
        }

        return $query;
    }

    /**
     * Sets to cache the timestamp of the next record to be published.
     * This value can be used to check if the cache is valid
     * @return string|bool Timestamp or `false`
     * @uses $cache
     */
    public function setNextToBePublished()
    {
        $next = $this->find()
            ->where([
                sprintf('%s.active', $this->alias()) => true,
                sprintf('%s.created >', $this->alias()) => new Time,
            ])
            ->order([sprintf('%s.created', $this->alias()) => 'ASC'])
            ->extract('created')
            ->first();

        $next = empty($next) ? false : $next->toUnixString();

        Cache::write('next_to_be_published', $next, $this->cache);

        return $next;
    }
}
