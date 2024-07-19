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
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @param string $id
     * @param string $name
     * @param string $icon
     */
    public function __construct(string $id, string $name, string $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->icon = $icon;
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
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     *
     * @return void
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
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
            self::getDataValue($data, 'name'),
            self::getDataValue($data, 'icon', null)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon
        ];
    }
}
