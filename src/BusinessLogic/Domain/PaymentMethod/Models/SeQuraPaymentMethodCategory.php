<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models;

use Exception;

class SeQuraPaymentMethodCategory
{
    /**
     * @var string String containing the title of the category.
     */
    protected $title;

    /**
     * @var string String containing the description of the category.
     */
    protected $description;

    /**
     * @var string|null String containing icon for the methods category in svg format.
     */
    protected $icon;

    /**
     * @var SeQuraPaymentMethod[] Array of payment methods.
     */
    protected $methods;

    /**
     * @param string $title
     * @param string $description
     * @param string|null $icon
     * @param SeQuraPaymentMethod[] $methods
     */
    public function __construct(string $title, string $description, ?string $icon, array $methods)
    {
        $this->title = $title;
        $this->description = $description;
        $this->icon = $icon;
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
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
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return SeQuraPaymentMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param SeQuraPaymentMethod[] $methods
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    /**
     * Creates an instance of SeQuraPaymentMethodCategory from given array data.
     *
     * @param array $data
     *
     * @return SeQuraPaymentMethodCategory
     *
     * @throws Exception
     */
    public static function fromArray(array $data): SeQuraPaymentMethodCategory
    {
        $methods = [];
        foreach ($data['methods'] as $method) {
            $methods[] = SeQuraPaymentMethod::fromArray($method);
        }

        return new self(
            $data['title'],
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $methods
        );
    }
}
