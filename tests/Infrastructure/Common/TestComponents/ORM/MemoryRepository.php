<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\MassInsert;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\IntermediateObject;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryCondition;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\Utility\EntityTranslator;
use SeQura\Core\Infrastructure\ORM\Utility\IndexHelper;

/**
 * Class MemoryRepository.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM
 */
class MemoryRepository implements RepositoryInterface, MassInsert
{
    /**
     * Fully qualified name of this class.
     */
    const THIS_CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * Executes select query
     *
     * @param QueryFilter $filter
     *
     * @return Entity[]
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function select(QueryFilter $filter = null)
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass();
        $type = $entity->getConfig()->getType();

        $fieldIndexMap = IndexHelper::mapFieldsToIndexes($entity);
        $groups = $filter ? $this->buildConditionGroups($filter, $fieldIndexMap) : array();

        $all = array_filter(
            MemoryStorage::$storage,
            function ($a) use ($type) {
                return $a['type'] === $type;
            }
        );

        $result = empty($groups) ? $all : array();
        foreach ($groups as $group) {
            $groupResult = $all;
            /** @var QueryCondition $condition */
            foreach ($group as $condition) {
                $groupResult = $this->filterByCondition($condition, $groupResult, $fieldIndexMap);
            }

            /** @noinspection SlowArrayOperationsInLoopInspection */
            $result = array_merge($result, $groupResult);
        }

        if (is_array($result) && !empty($result)) {
            $result = $this->unique($result);
        }

        if ($filter) {
            $this->sortResults($result, $filter, $fieldIndexMap);
            $result = $this->sliceResults($filter, $result);
        }

        return $this->translateToEntities($result);
    }

    /**
     * Executes select query and returns first result
     *
     * @param QueryFilter $filter
     *
     * @return Entity | null
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function selectOne(QueryFilter $filter = null)
    {
        if ($filter === null) {
            $filter = new QueryFilter();
        }

        $filter->setLimit(1);
        $results = $this->select($filter);

        return empty($results) ? null : $results[0];
    }

    /**
     * Executes insert query and returns id of created entity
     *
     * @param Entity $entity
     *
     * @return int
     */
    public function save(Entity $entity)
    {
        $id = MemoryStorage::generateId();
        $entity->setId($id);
        $this->saveEntityToStorage($entity);

        return $id;
    }

    /**
     * @inheritdoc
     */
    public function massInsert(array $entities)
    {
        foreach ($entities as $entity) {
            // This is mock implementation DO NOT implement this method like this in integrations unless it is unavoidable
            $this->save($entity);
        }
    }

    /**
     * Executes insert query and returns success flag
     *
     * @param Entity $entity
     *
     * @return bool
     */
    public function update(Entity $entity)
    {
        $result = $entity->getId() !== null && isset(MemoryStorage::$storage[$entity->getId()]);
        if ($result) {
            $this->saveEntityToStorage($entity);
        }

        return $result;
    }

    /**
     * Executes delete query and returns success flag
     *
     * @param Entity $entity
     *
     * @return bool
     */
    public function delete(Entity $entity)
    {
        $result = $entity->getId() !== null && isset(MemoryStorage::$storage[$entity->getId()]);
        if ($result) {
            unset(MemoryStorage::$storage[$entity->getId()]);
        }

        return $result;
    }

    /**
     * Returns full class name
     *
     * @return string
     */
    public static function getClassName()
    {
        return static::THIS_CLASS_NAME;
    }

    /**
     * Sets repository entity
     *
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Saves entity to the database.
     *
     * @param \SeQura\Core\Infrastructure\ORM\Entity $entity Entity to be saved
     */
    private function saveEntityToStorage(Entity $entity)
    {
        $indexes = IndexHelper::transformFieldsToIndexes($entity);
        $data = $entity->toArray();
        $data['class_name'] = $entity::getClassName();
        $data = json_encode($data);

        $storageItem = array(
            'id' => $entity->getId(),
            'type' => $entity->getConfig()->getType(),
            'index_1' => null,
            'index_2' => null,
            'index_3' => null,
            'index_4' => null,
            'index_5' => null,
            'index_6' => null,
            'index_7' => null,
            'index_8' => null,
            'index_9' => null,
            'index_10' => null,
            'data' => $data,
        );

        foreach ($indexes as $index => $value) {
            $storageItem['index_' . $index] = $value;
        }

        MemoryStorage::$storage[$entity->getId()] = $storageItem;
    }

    /**
     * @param QueryCondition $condition
     * @param array $groupResult
     * @param array $indexMap
     *
     * @return array
     */
    private function filterByCondition(QueryCondition $condition, array $groupResult, array $indexMap)
    {
        return array_filter(
            $groupResult,
            function ($item) use ($condition, $indexMap) {
                $column = $condition->getColumn();
                $indexKey = $column === 'id' ? 'id' : 'index_' . $indexMap[$column];
                $a = $item[$indexKey];
                if ($column === 'id') {
                    $b = $condition->getValue();
                } else {
                    $b = IndexHelper::castFieldValue($condition->getValue(), $condition->getValueType());
                }

                switch ($condition->getOperator()) {
                    case '=':
                        return $a === $b;
                    case '!=':
                        return $a !== $b;
                    case '>':
                        return $a > $b;
                    case '>=':
                        return $a >= $b;
                    case '<':
                        return $a < $b;
                    case '<=':
                        return $a <= $b;
                    case 'IN':
                        return in_array($a, $b, false);
                    case 'NOT IN':
                        return !in_array($a, $b, false);
                    case 'IS NULL':
                        return $a === null;
                    case 'IS NOT NULL':
                        return $a !== null;
                    case 'LIKE':
                        $firstP = strpos($b, '%');
                        $lastP = strrpos($b, '%');
                        $b = str_replace('%', '', $b);

                        // SEARCH - no %
                        if ($firstP === false) {
                            return $a === $b;
                        }

                        // SEARCH%
                        $position = strpos($a, $b);
                        if ($firstP > 0 && $firstP === $lastP) {
                            return $position === 0;
                        }

                        // %SEARCH%
                        if ($firstP === 0 && $lastP && $lastP > 0) {
                            return $position !== false;
                        }

                        // %SEARCH
                        if ($firstP === 0 && $firstP === $lastP) {
                            return $position !== false && $position + mb_strlen($b) === mb_strlen($a);
                        }

                        return false;
                    default:
                        return false;
                }
            }
        );
    }

    /**
     * @param array $result
     * @param QueryFilter $filter
     * @param array $fieldIndexMap
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function sortResults(array &$result, QueryFilter $filter, array $fieldIndexMap)
    {
        $column = $filter->getOrderByColumn();
        if (empty($column)) {
            return;
        }

        if ($column !== 'id' && !array_key_exists($column, $fieldIndexMap)) {
            throw new QueryFilterInvalidParamException(
                'Unknown or not indexed OrderBy column ' . $filter->getOrderByColumn()
            );
        }

        $direction = $filter->getOrderDirection();
        $indexKey = $column === 'id' ? 'id' : 'index_' . $fieldIndexMap[$column];

        $i = ($direction === 'ASC' ? 1 : -1);
        usort(
            $result,
            function ($first, $second) use ($i, $indexKey) {
                if ($first[$indexKey] === $second[$indexKey]) {
                    return 0;
                }

                return $first[$indexKey] < $second[$indexKey] ? -1 * $i : $i;
            }
        );
    }

    /**
     * @param QueryFilter $filter
     * @param array $result
     *
     * @return array
     */
    private function sliceResults(QueryFilter $filter, array $result)
    {
        if ($filter->getLimit()) {
            $result = array_slice($result, $filter->getOffset(), $filter->getLimit());
        }

        return $result;
    }

    /**
     * @param QueryFilter $filter
     * @param array $fieldIndexMap
     *
     * @return array
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function buildConditionGroups(QueryFilter $filter, array $fieldIndexMap)
    {
        $groups = array();
        $counter = 0;
        $fieldIndexMap['id'] = 0;
        foreach ($filter->getConditions() as $condition) {
            if (!empty($groups[$counter]) && $condition->getChainOperator() === 'OR') {
                $counter++;
            }

            // only index columns can be filtered
            if (!array_key_exists($condition->getColumn(), $fieldIndexMap)) {
                throw new QueryFilterInvalidParamException(
                    'Field ' . $condition->getColumn() . ' is not indexed in class ' . $this->entityClass
                );
            }

            $groups[$counter][] = $condition;
        }

        return $groups;
    }

    /**
     * @param array $result
     *
     * @return \SeQura\Core\Infrastructure\ORM\Entity[]
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     */
    private function translateToEntities(array $result)
    {
        $translator = new EntityTranslator();
        $translator->init($this->entityClass);

        /** @var IntermediateObject[] $intermediates */
        $intermediates = array();
        foreach ($result as $item) {
            $obj = new IntermediateObject();
            $obj->setData($item['data']);
            for ($i = 1; $i <= 10; $i++) {
                $obj->setIndexValue($i, $item['index_' . $i]);
            }

            $intermediates[] = $obj;
        }

        return $translator->translate($intermediates);
    }

    /**
     * Removes duplicate values from an array.
     *
     * @param array $array
     *
     * @return array
     */
    private function unique(array $array)
    {
        $result = array();
        $occurrences = array();

        foreach ($array as $item) {
            $fingerprint = md5(serialize($item));
            if (!in_array($fingerprint, $occurrences, true)) {
                $result[] = $item;
                $occurrences[] = $fingerprint;
            }
        }

        return $result;
    }

    /**
     * Counts records that match filter criteria.
     *
     * @param QueryFilter $filter Filter for query.
     *
     * @return int Number of records that match filter criteria.
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function count(QueryFilter $filter = null)
    {
        return count($this->select($filter));
    }
}
