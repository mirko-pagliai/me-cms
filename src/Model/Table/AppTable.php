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
use Cake\Event\Event;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Exception;

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
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options): void
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
    public function afterSave(Event $event, Entity $entity, ArrayObject $options): void
    {
        if ($this->getCacheName()) {
            Cache::clear(false, $this->getCacheName());
        }
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
        //Tries to transform the `created` string into a `Time` entity
        if (isset($data['created']) && is_string($data['created'])) {
            try {
                $created = new Time($data['created']);
            } catch (Exception $e) {
            }
        }
        elseif (array_key_exists('created', $data) && is_null($data['created'])) {
            $created = new Time();
        }
        if (isset($created)) {
            $data['created'] = $created;
        }
    }

    /**
     * "active" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findActive(Query $query, array $options): Query
    {
        return $query->where([
            sprintf('%s.active', $this->getAlias()) => true,
            sprintf('%s.created <=', $this->getAlias()) => new Time(),
        ]);
    }

    /**
     * "pending" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findPending(Query $query, array $options): Query
    {
        return $query->where(['OR' => [
            sprintf('%s.active', $this->getAlias()) => false,
            sprintf('%s.created >', $this->getAlias()) => new Time(),
        ]]);
    }

    /**
     * "random" find method
     * @param \Cake\ORM\Query $query Query object
     * @param array $options Options
     * @return \Cake\ORM\Query Query object
     */
    public function findRandom(Query $query, array $options): Query
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
        $values = $this->cache ?: null;

        if ($associations) {
            $values = [$values];
            foreach ($this->associations()->getIterator() as $association) {
                if (method_exists($association->getTarget(), 'getCacheName')
                    && $association->getTarget()->getCacheName()) {
                    $values[] = $association->getTarget()->getCacheName();
                }
            }

            $values = array_values(array_unique(array_filter($values)));
        }

        return $values;
    }

    /**
     * Gets records as list
     * @return \Cake\ORM\Query $query Query object
     * @uses getCacheName()
     */
    public function getList(): Query
    {
        return $this->find('list')
            ->order([$this->getDisplayField() => 'ASC'])
            ->cache(sprintf('%s_list', $this->getTable()), $this->getCacheName());
    }

    /**
     * Gets records as tree list
     * @return \Cake\ORM\Query $query Query object
     * @uses getCacheName()
     */
    public function getTreeList(): Query
    {
        return $this->find('treeList')
            ->cache(sprintf('%s_tree_list', $this->getTable()), $this->getCacheName());
    }

    /**
     * Build query from filter data
     * @param \Cake\ORM\Query $query Query object
     * @param array $data Filter data ($this->request->getQueryParams())
     * @return \Cake\ORM\Query $query Query object
     */
    public function queryFromFilter(Query $query, array $data = []): Query
    {
        //"ID" field
        if (!empty($data['id']) && is_positive($data['id'])) {
            $query->where([sprintf('%s.id', $this->getAlias()) => $data['id']]);
        }

        //"Title" field and "filename" fields
        foreach (['title', 'filename'] as $field) {
            if (!empty($data[$field]) && strlen($data[$field]) > 2) {
                $query->where([sprintf('%s.%s LIKE', $this->getAlias(), $field) => sprintf('%%%s%%', $data[$field])]);
            }
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
            $query->where([
                sprintf('%s.created >=', $this->getAlias()) => $start,
                sprintf('%s.created <', $this->getAlias()) => $start->addMonth(1),
            ]);
        }

        return $query;
    }
}
