<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Order;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\EventsWebhook;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Options;

/**
 * Interface MerchantDataProviderInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Order
 */
interface MerchantDataProviderInterface
{
    /**
     * @return ?string
     */
    public function getApprovedCallback(): ?string;

    /**
     * @return ?string
     */
    public function getRejectedCallback(): ?string;

    /**
     * @return string|null
     */
    public function getPartPaymentDetailsGetter(): ?string;

    /**
     * @return ?string
     */
    public function getNotifyUrl(): ?string;

    /**
     * @param string $cartId
     *
     * @return ?string
     */
    public function getReturnUrlForCartId(string $cartId): ?string;

    /**
     * @return ?string
     */
    public function getEditUrl(): ?string;

    /**
     * @return ?string
     */
    public function getAbortUrl(): ?string;

    /**
     * @return ?string
     */
    public function getApprovedUrl(): ?string;

    /**
     * @return ?Options
     */
    public function getOptions(): ?Options;

    /**
     * @return string
     */
    public function getEventsWebhookUrl(): string;

    /**
     * @return array<mixed, mixed>
     */
    public function getNotificationParametersForCartId(string $cartId): array;

    /**
     * @return array<mixed, mixed>
     */
    public function getEventsWebhookParametersForCartId(string $cartId): array;
}
