<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Options;

/**
 * Class MockMerchantDataProviderService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockMerchantDataProvider implements MerchantDataProviderInterface
{
    /**
     * @var ?string
     */
    private $approvedCallback = null;
    /**
     * @var ?string
     */
    private $rejectedCallback = null;
    /**
     * @var ?string
     */
    private $partPaymentDetailsGetter = null;
    /**
     * @var ?string
     */
    private $notifyUrl = null;
    /**
     * @var ?string
     */
    private $returnUrl = null;
    /**
     * @var ?string
     */
    private $editUrl = null;
    /**
     * @var ?string
     */
    private $abortUrl = null;

    /**
     * @var ?string $approvedUrl
     */
    private $approvedUrl;
    /**
     * @var ?Options $options
     */
    private $options;

    /**
     * @var string $eventsWebhook
     */
    private $eventsWebhook;

    /**
     * @var array<mixed, mixed>
     */
    private $notificationParameters;

    /**
     * @var array<mixed, mixed>
     */
    private $eventsWebhookParams;

    /**
     * @inheritDoc
     */
    public function getApprovedCallback(): ?string
    {
        return $this->approvedCallback;
    }

    /**
     * @param ?string $approvedCallback
     *
     * @return void
     */
    public function setMockApprovedCallback(?string $approvedCallback): void
    {
        $this->approvedCallback = $approvedCallback;
    }

    /**
     * @inheritDoc
     */
    public function getRejectedCallback(): ?string
    {
        return $this->rejectedCallback;
    }

    /**
     * @param ?string $rejectedCallback
     *
     * @return void
     */
    public function setMockRejectedCallback(?string $rejectedCallback): void
    {
        $this->rejectedCallback = $rejectedCallback;
    }

    /**
     * @inheritDoc
     */
    public function getPartPaymentDetailsGetter(): ?string
    {
        return $this->partPaymentDetailsGetter;
    }

    /**
     * @param ?string $partPaymentDetailsGetter
     *
     * @return void
     */
    public function setMockPartPaymentDetailsGetter(?string $partPaymentDetailsGetter): void
    {
        $this->partPaymentDetailsGetter = $partPaymentDetailsGetter;
    }

    /**
     * @inheritDoc
     */
    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    /**
     * @param ?string $notifyUrl
     *
     * @return void
     */
    public function setMockNotifyUrl(?string $notifyUrl): void
    {
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @inheritDoc
     */
    public function getReturnUrlForCartId(string $cartId): ?string
    {
        return $this->returnUrl;
    }

    /**
     * @param ?string $returnUrl
     *
     * @return void
     */
    public function setMockReturnUrl(?string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @inheritDoc
     */
    public function getEditUrl(): ?string
    {
        return $this->editUrl;
    }

    /**
     * @param ?string $editUrl
     *
     * @return void
     */
    public function setMockEditUrl(?string $editUrl): void
    {
        $this->editUrl = $editUrl;
    }

    /**
     * @inheritDoc
     */
    public function getAbortUrl(): ?string
    {
        return $this->abortUrl;
    }

    /**
     * @param ?string $abortUrl
     *
     * @return void
     */
    public function setMockAbortUrl(?string $abortUrl): void
    {
        $this->abortUrl = $abortUrl;
    }

    /**
     * @inheritDoc
     */
    public function getApprovedUrl(): ?string
    {
        return $this->approvedUrl;
    }

    /**
     * @param ?string $approvedUrl
     *
     * @return void
     */
    public function setMockApprovedUrl(?string $approvedUrl): void
    {
        $this->approvedUrl = $approvedUrl;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): ?Options
    {
        return $this->options;
    }

    /**
     * @param ?Options $options
     *
     * @return void
     */
    public function setMockOptions(?Options $options): void
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function getEventsWebhookUrl(): string
    {
        return $this->eventsWebhook;
    }

    /**
     * @param string $eventsWebhookUrl
     *
     * @return void
     */
    public function setMockEventsWebhookUrl(string $eventsWebhookUrl): void
    {
        $this->eventsWebhook = $eventsWebhookUrl;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationParametersForCartId(string $cartId): array
    {
        return $this->notificationParameters;
    }

    /**
     * @param array $notificationParameters
     *
     * @return void
     */
    public function setMockNotificationParameters(array $notificationParameters): void
    {
        $this->notificationParameters = $notificationParameters;
    }

    /**
     * @inheritDoc
     */
    public function getEventsWebhookParametersForCartId(string $cartId): array
    {
        return $this->eventsWebhookParams;
    }

    /**
     * @param array $eventsWebhookParams
     *
     * @return void
     */
    public function setMockEventsWebhookParameters(array $eventsWebhookParams): void
    {
        $this->eventsWebhookParams = $eventsWebhookParams;
    }
}
