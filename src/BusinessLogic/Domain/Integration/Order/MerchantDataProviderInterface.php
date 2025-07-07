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
     * @return ?string
     */
    public function getReturnUrl(): ?string;

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
    public function getNotificationParameters(): array;

    /**
     * @return array<mixed, mixed>
     */
    public function getEventsWebhookParameters(): array;
}
