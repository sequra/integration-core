<?php

namespace SeQura\Core\Infrastructure\ORM\Utility;

use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException;
use SeQura\Core\Infrastructure\ORM\IntermediateObject;

/**
 * Class EntityTranslator
 *
 * @package SeQura\Core\Infrastructure\ORM\Utility
 */
class EntityTranslator
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param string $entityClass
     *
     * @throws EntityClassException
     */
    public function init($entityClass)
    {
        if (!is_subclass_of($entityClass, Entity::getClassName())) {
            throw new EntityClassException("Class $entityClass is not implementation of Entity");
        }

        $this->entityClass = $entityClass;
    }

    /**
     * Translate intermediate objects to concrete entities
     *
     * @param IntermediateObject[] $intermediateObjects
     *
     * @return Entity[]
     * @throws EntityClassException
     */
    public function translate(array $intermediateObjects)
    {
        if ($this->entityClass === null) {
            throw new EntityClassException('Entity translator must be initialized with entity class.');
        }

        $result = array();
        foreach ($intermediateObjects as $intermediateObject) {
            $result[] = $this->translateOne($intermediateObject);
        }

        return $result;
    }

    /**
     * Translates one intermediate object to concrete object
     *
     * @param IntermediateObject $intermediateObject
     *
     * @return Entity
     * @throws EntityClassException
     */
    protected function translateOne(IntermediateObject $intermediateObject)
    {
        $data = json_decode($intermediateObject->getData(), true);

        if (empty($data['class_name'])) {
            throw new EntityClassException('Entity has not provided class name.');
        }

        /**
         * @var Entity $entity
        */
        $entity = new $data['class_name']();
        $entity->inflate($data);
        if (!($entity instanceof $this->entityClass)) {
            throw new EntityClassException("Unserialized entity is not of class {$this->entityClass}");
        }

        return $entity;
    }
}
