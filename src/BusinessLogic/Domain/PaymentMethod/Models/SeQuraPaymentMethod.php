<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models;

use DateTime;
use Exception;

/**
 * Class SeQuraPaymentMethod
 *
 * @package SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models
 */
class SeQuraPaymentMethod
{
    /**
     * @var string SeQura product code.
     */
    protected $product;

    /**
     * @var string|null SeQura campaign code.
     */
    protected $campaign;

    /**
     * @var string Payment method title.
     */
    protected $title;

    /**
     * @var string Longer payment method title.
     */
    protected $longTitle;

    /**
     * @var string|null Payment method claim.
     */
    protected $claim;

    /**
     * @var string|null Payment method description.
     */
    protected $description;

    /**
     * @var string|null Serialized HTML element.
     */
    protected $icon;

    /**
     * @var SeQuraCost
     */
    protected $cost;

    /**
     * @var string|null Cost description.
     */
    protected $costDescription;

    /**
     * @var DateTime Method available from this date on.
     */
    protected $startsAt;

    /**
     * @var DateTime Method available until this date.
     */
    protected $endsAt;

    /**
     * @var int|null Minimum supported order amount for this payment method.
     */
    protected $minAmount;

    /**
     * @var int|null Maximum supported order amount for this payment method.
     */
    protected $maxAmount;

    /**
     * @param string $product
     * @param string $title
     * @param string $longTitle
     * @param SeQuraCost $cost
     * @param DateTime $startsAt
     * @param DateTime $endsAt
     * @param string|null $campaign
     * @param string|null $claim
     * @param string|null $description
     * @param string|null $icon
     * @param string|null $costDescription
     * @param int|null $minAmount
     * @param int|null $maxAmount
     */
    public function __construct(
        string $product,
        string $title,
        string $longTitle,
        SeQuraCost $cost,
        DateTime $startsAt,
        DateTime $endsAt,
        string $campaign = null,
        string $claim = null,
        string $description = null,
        string $icon = null,
        string $costDescription = null,
        int $minAmount = null,
        int $maxAmount = null
    ) {
        $this->product = $product;
        $this->title = $title;
        $this->longTitle = $longTitle;
        $this->cost = $cost;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->campaign = $campaign;
        $this->claim = $claim;
        $this->description = $description;
        $this->icon = $icon;
        $this->costDescription = $costDescription;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string|null
     */
    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    /**
     * @param string|null $campaign
     */
    public function setCampaign(?string $campaign): void
    {
        $this->campaign = $campaign;
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
    public function getLongTitle(): string
    {
        return $this->longTitle;
    }

    /**
     * @param string $longTitle
     */
    public function setLongTitle(string $longTitle): void
    {
        $this->longTitle = $longTitle;
    }

    /**
     * @return string|null
     */
    public function getClaim(): ?string
    {
        return $this->claim;
    }

    /**
     * @param string|null $claim
     */
    public function setClaim(?string $claim): void
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
     * @param string|null $description
     */
    public function setDescription(?string $description): void
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
     * @return SeQuraCost
     */
    public function getCost(): SeQuraCost
    {
        return $this->cost;
    }

    /**
     * @param SeQuraCost $cost
     */
    public function setCost(SeQuraCost $cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return string|null
     */
    public function getCostDescription(): ?string
    {
        return $this->costDescription;
    }

    /**
     * @param string|null $costDescription
     */
    public function setCostDescription(?string $costDescription): void
    {
        $this->costDescription = $costDescription;
    }

    /**
     * @return DateTime
     */
    public function getStartsAt(): DateTime
    {
        return $this->startsAt;
    }

    /**
     * @param DateTime $startsAt
     */
    public function setStartsAt(DateTime $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    /**
     * @return DateTime
     */
    public function getEndsAt(): DateTime
    {
        return $this->endsAt;
    }

    /**
     * @param DateTime $endsAt
     */
    public function setEndsAt(DateTime $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    /**
     * @return int|null
     */
    public function getMinAmount(): ?int
    {
        return $this->minAmount;
    }

    /**
     * @param int|null $minAmount
     */
    public function setMinAmount(?int $minAmount): void
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
     * @param int|null $maxAmount
     */
    public function setMaxAmount(?int $maxAmount): void
    {
        $this->maxAmount = $maxAmount;
    }

    /**
     * Creates an instance of SeQuraPaymentMethod from given array data.
     *
     * @param array $data
     *
     * @return SeQuraPaymentMethod
     *
     * @throws Exception
     */
    public static function fromArray(array $data): SeQuraPaymentMethod
    {
        $cost = new SeQuraCost(
            $data['cost']['setup_fee'],
            $data['cost']['instalment_fee'],
            $data['cost']['down_payment_fees'],
            $data['cost']['instalment_total']
        );

        return new self(
            $data['product'],
            $data['title'],
            $data['long_title'],
            $cost,
            new DateTime($data['starts_at']),
            new DateTime($data['ends_at']),
            $data['campaign'] ?? null,
            $data['claim'] ?? null,
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['cost_description'] ?? null,
            $data['min_amount'] ?? null,
            $data['max_amount'] ?? null
        );
    }
}
