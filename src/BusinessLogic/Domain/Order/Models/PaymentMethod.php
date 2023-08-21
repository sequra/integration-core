<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class PaymentMethod
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class PaymentMethod extends DataTransferObject
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Create a new PaymentMethod instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return PaymentMethod Returns a new PaymentMethod instance.
     */
    public static function fromArray(array $data): PaymentMethod
    {
        return new self(
            self::getDataValue($data, 'id'),
            self::getDataValue($data, 'name')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
