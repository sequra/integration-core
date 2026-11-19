<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

use InvalidArgumentException;

/**
 * AbstractItemFactory
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
abstract class AbstractItemFactory
{
    /**
     * Create Item object from array.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidArgumentException
     */
    abstract public function createFromArray(array $data): Item;

    /**
     * Create a list of Item objects from an array of data.
     *
     * @param array<array<string, mixed>> $data Array of arrays containing the data of the items.
     *
     * @throws InvalidArgumentException
     *
     * @return Item[]
     */
    public function createListFromArray(array $data): array
    {
        $items = [];
        foreach ($data as $itemData) {
            $item = $this->createFromArray($itemData);
            if ($item !== null) {
                $items[] = $item;
            }
        }
        return $items;
    }
}
