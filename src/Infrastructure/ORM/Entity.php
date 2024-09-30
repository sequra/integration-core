<?php

namespace SeQura\Core\Infrastructure\ORM;

use InvalidArgumentException;
use SeQura\Core\Infrastructure\Data\DataTransferObject;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;

/**
 * Class Entity.
 *
 * @package SeQura\Core\Infrastructure\ORM\Entities
 */
/**
 * @phpstan-consistent-constructor
 */
abstract class Entity extends DataTransferObject
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Entity identifier.
     *
     * @var int
     */
    protected $id;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array('id');

    public function __construct()
    {
    }

    /**
     * Returns full class name.
     *
     * @return string Fully qualified class name.
     */
    public static function getClassName(): string
    {
        return static::CLASS_NAME;
    }

    /**
     * Transforms raw array data to this entity instance.
     *
     * @param mixed[] $data Raw array data with keys for class fields. @see self::$fields for field names.
     *
     * @return static Transformed entity object.
     */
    public static function fromArray(array $data): Entity
    {
        $instance = new static();
        $instance->inflate($data);

        return $instance;
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    abstract public function getConfig(): EntityConfiguration;

    /**
     * Sets raw array data to this entity instance properties.
     *
     * @param mixed[] $data Raw array data with keys for class fields. @see self::$fields for field names.
     */
    public function inflate(array $data): void
    {
        foreach ($this->fields as $fieldName) {
            $this->$fieldName = static::getArrayValue($data, $fieldName, $this->$fieldName);
        }
    }

    /**
     * Transforms entity to its array format representation.
     *
     * @return mixed[] Entity in array format.
     */
    public function toArray(): array
    {
        $data = array('class_name' => static::getClassName());
        foreach ($this->fields as $fieldName) {
            $data[$fieldName] = $this->$fieldName;
        }

        return $data;
    }

    /**
     * Gets entity identifier.
     *
     * @return int Identifier.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets entity identifier.
     *
     * @param int $id entity identifier.
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Gets instance value for given index key.
     *
     * @param string $indexKey Name of index column.
     *
     * @return mixed Value for index.
     */
    public function getIndexValue($indexKey)
    {
        $methodName = 'get' . ucfirst($indexKey);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        $methodName = 'is' . ucfirst($indexKey);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        if (property_exists($this, $indexKey)) {
            return $this->$indexKey;
        }

        throw new InvalidArgumentException('Neither field not getter found for index "' . $indexKey . '".');
    }

    /**
     * Gets value from the array for given key.
     *
     * @param mixed[] $search An array with keys to check.
     * @param string $key Key to get value for.
     * @param mixed $default Default value if key is not present. NULL by default.
     *
     * @return mixed Value from the array for given key if key exists; otherwise, $default value.
     */
    protected static function getArrayValue($search, $key, $default = null)
    {
        return array_key_exists($key, $search) ? $search[$key] : $default;
    }
}
