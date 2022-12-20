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

use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\ORM\Association;
use Cake\ORM\Query as CakeQuery;
use Cake\ORM\Table;
use MeCms\ORM\Query;
use Tools\Exceptionist;

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
    protected string $cache;

    /**
     * Called after an entity has been deleted
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return void
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
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        $this->clearCache();
    }

    /**
     * Delete all keys from the cache
     * @return bool `true` if the cache was successfully cleared, `false` otherwise
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
            ->andWhere([sprintf('%s.created <=', $this->getAlias()) => new FrozenTime()]);
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
            sprintf('%s.created >', $this->getAlias()) => new FrozenTime(),
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
     * @return string
     * @since 2.26.0
     */
    public function getCacheName(): string
    {
        return $this->cache ?? '';
    }

    /**
     * Gets the cache configuration name used by this table and its associated tables
     * @return string[]
     * @since 2.30.13
     */
    public function getCacheNameWithAssociated(): array
    {
        $values = array_map(function (string $name): string {
            /** @var \MeCms\Model\Table\AppTable $table */
            $table = $this->$name->getTarget();

            return method_exists($table, 'getCacheName') ? $table->getCacheName() : '';
        }, $this->associations()->keys());

        return array_clean([$this->getCacheName(), ...$values]);
    }

    /**
     * Gets records as list
     * @return \Cake\ORM\Query $query Query object
     */
    public function getList(): CakeQuery
    {
        return $this->find('list')
            ->orderAsc(Exceptionist::isString($this->getDisplayField()))
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
        if (!empty($data['created']) && preg_match('/^[1-9]\d{3}-[01]\d$/', $data['created'])) {
            $start = new FrozenTime(sprintf('%s-01', $data['created']));
            $query->where([sprintf('%s.created >=', $this->getAlias()) => $start])
                ->andWhere([sprintf('%s.created <', $this->getAlias()) => $start->addMonth()]);
        }

        return $query;
    }
}
