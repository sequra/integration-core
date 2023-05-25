<?php

namespace SeQura\Core\BusinessLogic\Domain\Webhook\Models;

/**
 * Class Webhook
 *
 * @package SeQura\Core\BusinessLogic\Domain\Webhook\Models
 */
class Webhook
{
    /**
     * @var string The signature string.
     */
    public $signature;

    /**
     * @var string The order reference.
     */
    public $orderRef;

    /**
     * @var string The product code.
     */
    public $productCode;

    /**
     * @var string The state of the order.
     */
    public $sqState;

    /**
     * @var string The secondary order reference.
     */
    public $orderRef1;

    /**
     * @var int|null The number of days since approval.
     */
    public $approvedSince;

    /**
     * @var int|null The number of days since need for review.
     */
    public $needsReviewSince;

    /**
     * Transforms array into a webhook object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return Webhook  Instance of serialized object.
     */
    public static function fromArray(array $array): Webhook
    {
        $webhook = new static();

        $webhook->setSignature($array['signature'] ?? '');
        $webhook->setOrderRef($array['order_ref'] ?? '');
        $webhook->setProductCode($array['product_code'] ?? '');
        $webhook->setSqState($array['sq_state'] ?? '');
        $webhook->setOrderRef1($array['order_ref_1'] ?? '');
        $webhook->setApprovedSince($array['approved_since'] ?? null);
        $webhook->setNeedsReviewSince($array['needs_review_since'] ?? null);

        return $webhook;
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray(): array
    {
        $data['signature'] = $this->getSignature();
        $data['order_ref'] = $this->getOrderRef();
        $data['product_code'] = $this->getProductCode();
        $data['sq_state'] = $this->getSqState();
        $data['order_ref_1'] = $this->getOrderRef1();
        $this->getApprovedSince() && $data['approved_since'] = $this->getApprovedSince();
        $this->getNeedsReviewSince() && $data['needs_review_since'] = $this->getNeedsReviewSince();

        return $data;
    }

    /**
     * Get the value of signature.
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Set the value of signature.
     *
     * @param string $signature
     */
    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * Get the value of orderRef.
     *
     * @return string
     */
    public function getOrderRef(): string
    {
        return $this->orderRef;
    }

    /**
     * Set the value of orderRef.
     *
     * @param string $orderRef
     */
    public function setOrderRef(string $orderRef): void
    {
        $this->orderRef = $orderRef;
    }

    /**
     * Get the value of productCode.
     *
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * Set the value of productCode.
     *
     * @param string $productCode
     */
    public function setProductCode(string $productCode): void
    {
        $this->productCode = $productCode;
    }

    /**
     * Get the value of sqState.
     *
     * @return string
     */
    public function getSqState(): string
    {
        return $this->sqState;
    }

    /**
     * Set the value of sqState.
     *
     * @param string $sqState
     */
    public function setSqState(string $sqState): void
    {
        $this->sqState = $sqState;
    }

    /**
     * Get the value of orderRef1.
     *
     * @return string
     */
    public function getOrderRef1(): string
    {
        return $this->orderRef1;
    }

    /**
     * Set the value of orderRef1.
     *
     * @param string $orderRef1
     */
    public function setOrderRef1(string $orderRef1): void
    {
        $this->orderRef1 = $orderRef1;
    }

    /**
     * Gets the value of approvedSince.
     *
     * @return int|null
     */
    public function getApprovedSince(): ?int
    {
        return $this->approvedSince;
    }

    /**
     * Set the value of approvedSince.
     *
     * @param int|null $approvedSince
     */
    public function setApprovedSince(?int $approvedSince): void
    {
        $this->approvedSince = $approvedSince;
    }

    /**
     * Gets the value of needsReviewSince.
     *
     * @return int|null
     */
    public function getNeedsReviewSince(): ?int
    {
        return $this->needsReviewSince;
    }

    /**
     * Set the value of needsReviewSince.
     *
     * @param int|null $needsReviewSince
     */
    public function setNeedsReviewSince(?int $needsReviewSince): void
    {
        $this->needsReviewSince = $needsReviewSince;
    }
}
