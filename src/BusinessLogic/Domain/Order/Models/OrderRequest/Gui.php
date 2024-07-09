<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidGuiLayoutValueException;

/**
 * Class Gui
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Gui extends OrderRequestDTO
{
    /**
     * Available values for layout property.
     */
    public const ALLOWED_VALUES = ['desktop' => 'desktop', 'phone' => 'smartphone'];

    /**
     * @var string Type of layout the API should optimise for.
     */
    protected $layout;

    /**
     * @param string $layout
     *
     * @throws InvalidGuiLayoutValueException
     */
    public function __construct(string $layout)
    {
        if (!in_array($layout, self::ALLOWED_VALUES)) {
            throw new InvalidGuiLayoutValueException(
                'Layout value must be one of the values defined in the allowed values constant.'
            );
        }

        $this->layout = $layout;
    }

    /**
     * Creates a new Gui instance from an input array.
     *
     * @param array $data Input data.
     *
     * @return Gui
     * @throws InvalidGuiLayoutValueException
     */
    public static function fromArray(array $data): Gui
    {
        $layout = self::getDataValue($data, 'layout', 'desktop');

        if (!in_array($layout, self::ALLOWED_VALUES, true)) {
            throw new InvalidGuiLayoutValueException(
                'Layout value must be one of the values defined in the allowed values constant.'
            );
        }

        return new self($layout);
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
