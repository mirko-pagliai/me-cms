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
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Association;
use Cake\ORM\Query as CakeQuery;
use Cake\ORM\Table;
use Exception;
use MeCms\ORM\Query;

/**
 * Application table class
 * @method findActiveById($id)
 * @method findById($id)
 * @method findPendingById($id)
 */
abstract class AppTable extends Table
{
    /**
     * Cache configuration name
     * @var string
     */
    protected $cache;

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     * @uses clearCache()
     */
    public function afterDelete(Event $event, EntityInterface $entity): void
    {
        $this->clearCache();
    }

    /**
     * Called after an entity is saved
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
     * @uses clearCache()
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        $this->clearCache();
    }

    /**
     * Called before request data is converted into entities
     * @param \Cake\Event\Event $event Event object
     * @param \ArrayObject $data Request data
     * @param \ArrayObject $options Options
     * @return void
     * @since 2.26.6
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options): void
    {
        if (array_key_exists('created', $data->getArrayCopy())) {
            if (is_string($data['created'])) {
                try {
                    $data['created'] = new Time($data['created']);
                } catch (Exception $e) {
                }
            } elseif (empty($data['created'])) {
                $data['created'] = new Time();
            }
        }
    }

    /**
     * Delete all keys from the cache
     * @return bool `true` if the cache was successfully cleared, `false` otherwise
     * @uses getCacheName()
     */
    public function clearCache(): bool
    {
        return Cache::clear($this->getCacheName());
    }

    /**
     * Deletes all records matching the provided conditions
     * @param mixed $conditions Conditions to be used, accepts anything
     *  `Query::where()` can take
     * @return int Returns the number of affected rows
     * @uses clearCache()
     */
    public function deleteAll($conditions): int
    {
        $this->clearCache();

        return parent::deleteAll($conditions);
    }

    /**
     * "active" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findActive(Query $query): Query
    {
        return $query->where([sprintf('%s.active', $this->getAlias()) => true])
            ->andWhere([sprintf('%s.created <=', $this->getAlias()) => new Time()]);
    }

    /**
     * "pending" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findPending(Query $query): Query
    {
        return $query->where(['OR' => [
            sprintf('%s.active', $this->getAlias()) => false,
            sprintf('%s.created >', $this->getAlias()) => new Time(),
        ]]);
    }

    /**
     * "random" find method
     * @param \MeCms\ORM\Query $query Query object
     * @return \MeCms\ORM\Query $query Query object
     */
    public function findRandom(Query $query): Query
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
    public function getCacheName(bool $associations = false)
    {
        if (!$associations) {
            return $this->cache ?: null;
        }

        $values = collection($this->associations()->getIterator())
            ->filter(function (Association $association) {
                return method_exists($association->getTarget(), 'getCacheName');
            })
            ->map(function (Association $association) {
                /** @var \MeCms\Model\Table\AppTable $target */
                $target = $association->getTarget();

                return $target->getCacheName();
            })
            ->prependItem($this->cache ?: null);

        return array_values(array_unique($values->toList()));
    }

    /**
     * Gets records as list
     * @return \Cake\ORM\Query $query Query object
     */
    public function getList(): CakeQuery
    {
        return $this->find('list')
            ->orderAsc($this->getDisplayField())
            ->cache($this->getTable() . '_list');
    }

    /**
     * Gets records as tree list
     * @return \Cake\ORM\Query $query Query object
     */
    public function getTreeList(): CakeQuery
    {
        return $this->find('treeList')->cache($this->getTable() . '_tree_list');
    }

    /**
     * Creates a new Query instance for a table
     * @return \Cake\ORM\Query
     * @since 2.27.1
     */
    public function query(): CakeQuery
    {
        return new Query($this->getConnection(), $this);
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data (`$this->getRequest()->getQueryParams()`)
     * @return \Cake\ORM\Query $query Query object
     */
    public function queryFromFilter(CakeQuery $query, array $data = []): CakeQuery
    {
        //"ID" field
        if (!empty($data['id']) && is_positive($data['id'])) {
            $query->where([sprintf('%s.id', $this->getAlias()) => $data['id']]);
        }

        //"Title" field
        if (!empty($data['title']) && strlen($data['title']) > 2) {
            $query->where([sprintf('%s.%s LIKE', $this->getAlias(), 'title') => '%' . $data['title'] . '%']);
        }

        //"User" (author) and "category" fields
        foreach (['user', 'category'] as $field) {
            if (!empty($data[$field]) && is_positive($data[$field])) {
                $query->where([sprintf('%s.%s_id', $this->getAlias(), $field) => $data[$field]]);
            }
        }

        //"Active" field
        if (!empty($data['active'])) {
            $query->where([sprintf('%s.active', $this->getAlias()) => $data['active'] === I18N_YES]);
        }

        //"Priority" field
        if (!empty($data['priority']) && $data['priority'] > 0 && $data['priority'] <= 5) {
            $query->where([sprintf('%s.priority', $this->getAlias()) => $data['priority']]);
        }

        //"Created" field
        if (!empty($data['created']) && preg_match('/^[1-9]\d{3}\-[01]\d$/', $data['created'])) {
            $start = new FrozenTime(sprintf('%s-01', $data['created']));
            $query->where([sprintf('%s.created >=', $this->getAlias()) => $start])
                ->andWhere([sprintf('%s.created <', $this->getAlias()) => $start->addMonth(1)]);
        }

        return $query;
    }
}
