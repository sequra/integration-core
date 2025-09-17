<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

/**
 * Class RegistrationItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class RegistrationItem extends Item
{
    /**
     * @var string|int A unique code that refers to this registration.
     */
    protected $reference;

    /**
     * @var string A name to describe this registration.
     */
    protected $name;

    /**
     * @param int|string $reference
     * @param string $name
     * @param int $totalWithTax
     */
    public function __construct($reference, string $name, int $totalWithTax)
    {
        parent::__construct($totalWithTax, ItemType::TYPE_REGISTRATION);

        $this->reference = $reference;
        $this->name = $name;
    }

    /**
     * @param mixed[] $data
     *
     * @return OtherPaymentItem
     */
    public static function fromArray(array $data): Item
    {
        $reference = self::getDataValue($data, 'reference');
        $name = self::getDataValue($data, 'name');
        $totalWithTax = self::getDataValue($data, 'total_with_tax', 0);

        return new self($reference, $name, $totalWithTax);
    }

    /**
     * @return int|string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
