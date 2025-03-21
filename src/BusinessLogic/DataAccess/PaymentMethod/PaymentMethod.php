<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PaymentMethod;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class PaymentMethod
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PaymentMethod
 */
class PaymentMethod extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $product;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $longTitle;
    /**
     * @var string
     */
    protected $startsAt;
    /**
     * @var string
     */
    protected $endsAt;
    /**
     * @var string
     */
    protected $campaign;
    /**
     * @var string
     */
    protected $claim;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $icon;
    /**
     * @var string
     */
    protected $costDescription;
    /**
     * @var int
     */
    protected $minAmount;
    /**
     * @var int
     */
    protected $maxAmount;
    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('product');

        return new EntityConfiguration($indexMap, 'PaymentMethod');
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->product = $data['product'];
        $this->title = $data['title'];
        $this->longTitle = $data['long_title'];
        $this->startsAt = $data['starts_at'];
        $this->endsAt = $data['ends_at'];
        $this->campaign = $data['campaign'];
        $this->claim = $data['claim'];
        $this->description = $data['description'];
        $this->icon = $data['icon'];
        $this->costDescription = $data['cost_description'];
        $this->minAmount = $data['min_amount'];
        $this->maxAmount = $data['max_amount'];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['product'] = $this->product;
        $data['store_id'] = $this->storeId;
        $data['title'] = $this->title;
        $data['long_title'] = $this->longTitle;
        $data['starts_at'] = $this->startsAt;
        $data['ends_at'] = $this->endsAt;
        $data['campaign'] = $this->campaign;
        $data['claim'] = $this->claim;
        $data['description'] = $this->description;
        $data['icon'] = $this->icon;
        $data['cost_description'] = $this->costDescription;
        $data['min_amount'] = $this->minAmount;
        $data['max_amount'] = $this->maxAmount;

        return $data;
    }

    /**
     * @return string|null
     */
    public function getProduct(): ?string
    {
        return $this->product;
    }

    /**
     * @param string $product
     * @return void
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getLongTitle(): ?string
    {
        return $this->longTitle;
    }

    /**
     * @param string $longTitle
     * @return void
     */
    public function setLongTitle(string $longTitle): void
    {
        $this->longTitle = $longTitle;
    }

    /**
     * @return string|null
     */
    public function getStartsAt(): ?string
    {
        return $this->startsAt;
    }

    /**
     * @param string $startsAt
     * @return void
     */
    public function setStartsAt(string $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    /**
     * @return string|null
     */
    public function getEndsAt(): ?string
    {
        return $this->endsAt;
    }

    /**
     * @param string $endsAt
     * @return void
     */
    public function setEndsAt(string $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    /**
     * @return string|null
     */
    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    /**
     * @param string $campaign
     * @return void
     */
    public function setCampaign(string $campaign): void
    {
        $this->campaign = $campaign;
    }

    /**
     * @return string|null
     */
    public function getClaim(): ?string
    {
        return $this->claim;
    }

    /**
     * @param string $claim
     * @return void
     */
    public function setClaim(string $claim): void
    {
        $this->claim = $claim;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
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
     * @param string $icon
     * @return void
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string|null
     */
    public function getCostDescription(): ?string
    {
        return $this->costDescription;
    }

    /**
     * @param string $costDescription
     * @return void
     */
    public function setCostDescription(string $costDescription): void
    {
        $this->costDescription = $costDescription;
    }

    /**
     * @return int|null
     */
    public function getMinAmount(): ?int
    {
        return $this->minAmount;
    }

    /**
     * @param int $minAmount
     * @return void
     */
    public function setMinAmount(int $minAmount): void
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return int|null
     */
    public function getMaxAmount(): ?int
    {
        return $this->maxAmount;
    }

    /**
     * @param int $maxAmount
     * @return void
     */
    public function setMaxAmount(int $maxAmount): void
    {
        $this->maxAmount = $maxAmount;
    }

    public function getStoreId(): string
    {
        return $this->storeId;
    }

    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }
}
