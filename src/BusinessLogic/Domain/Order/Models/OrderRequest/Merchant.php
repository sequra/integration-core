<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class Merchant
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Merchant extends OrderRequestDTO
{
    /**
     * @var string|int Merchant identifier.
     */
    protected $id;

    /**
     * @var string|null SeQura will make an IPN POST to this URL when the order is approved.
     */
    protected $notifyUrl;

    /**
     * @var string[]|null Optional name/value pairs that will be included in the IPN POST.
     */
    protected $notificationParameters;

    /**
     * @var string|null The shopper will be redirected to this URL once the shop has confirmed the order after IPN
     * notification.
     */
    protected $returnUrl;

    /**
     * @var string|null Name of Javascript function to call when SeQura approves the order and checkout should move to
     * next step.
     */
    protected $approvedCallback;

    /**
     * @var string|null URL for a page where the shopper can edit their name, address, etc.
     */
    protected $editUrl;

    /**
     * @var string|null URL for a page where the shopper can pick another payment method.
     */
    protected $abortUrl;

    /**
     * @var string|null Name of Javascript function to call if the shopper is rejected.
     */
    protected $rejectedCallback;

    /**
     * @var string|null Name of Javascript function to call to get the shopper's part-payment details.
     */
    protected $partpaymentDetailsGetter;

    /**
     * @var string|null When SeQura approves the order, the customer's browser will make a POST to this URL without
     * arguments.
     */
    protected $approvedUrl;

    /**
     * @var Options|null Features activated by this merchant in this request.
     */
    protected $options;

    /**
     * @var EventsWebhook|null Fields describing how the merchant wants to receive webhook events.
     */
    protected $eventsWebhook;

    /**
     * @param int|string $id
     * @param string|null $notifyUrl
     * @param string|null $returnUrl
     * @param string|null $approvedCallback
     * @param string|null $editUrl
     * @param string|null $abortUrl
     * @param string|null $rejectedCallback
     * @param string|null $partpaymentDetailsGetter
     * @param string|null $approvedUrl
     * @param Options|null $options
     * @param EventsWebhook|null $eventsWebhook
     *
     * @throws InvalidUrlException
     */
    public function __construct(
        $id,
        string $notifyUrl = null,
        array $notificationParameters = null,
        string $returnUrl = null,
        string $approvedCallback = null,
        string $editUrl = null,
        string $abortUrl = null,
        string $rejectedCallback = null,
        string $partpaymentDetailsGetter = null,
        string $approvedUrl = null,
        Options $options = null,
        EventsWebhook $eventsWebhook = null
    ) {
        if ($notifyUrl && !StringValidator::isValidUrl($notifyUrl)) {
            throw new InvalidUrlException('NotifyUrl must be a valid url.');
        }

        $this->id = $id;
        $this->notifyUrl = $notifyUrl;
        $this->notificationParameters = $notificationParameters;
        $this->returnUrl = $returnUrl;
        $this->approvedCallback = $approvedCallback;
        $this->editUrl = $editUrl;
        $this->abortUrl = $abortUrl;
        $this->rejectedCallback = $rejectedCallback;
        $this->partpaymentDetailsGetter = $partpaymentDetailsGetter;
        $this->approvedUrl = $approvedUrl;
        $this->options = $options;
        $this->eventsWebhook = $eventsWebhook;
    }

    /**
     * Create a new Merchant instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Merchant Returns a new Merchant instance.
     * @throws InvalidUrlException
     */
    public static function fromArray(array $data): Merchant
    {
        $options = self::getDataValue($data, 'options', null);
        if ($options) {
            $options = Options::fromArray($options);
        }

        $eventsWebhook = self::getDataValue($data, 'events_webhook', null);
        if ($eventsWebhook) {
            $eventsWebhook = EventsWebhook::fromArray($eventsWebhook);
        }

        return new self(
            self::getDataValue($data, 'id', null),
            self::getDataValue($data, 'notify_url', null),
            self::getDataValue($data, 'notification_parameters', null),
            self::getDataValue($data, 'return_url', null),
            self::getDataValue($data, 'approved_callback', null),
            self::getDataValue($data, 'edit_url', null),
            self::getDataValue($data, 'abort_url', null),
            self::getDataValue($data, 'rejected_callback', null),
            self::getDataValue($data, 'partpayment_details_getter', null),
            self::getDataValue($data, 'approved_url', null),
            $options,
            $eventsWebhook
        );
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    /**
     * @return string[]|null
     */
    public function getNotificationParameters(): ?array
    {
        return $this->notificationParameters;
    }

    /**
     * @return string|null
     */
    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * @return string|null
     */
    public function getApprovedCallback(): ?string
    {
        return $this->approvedCallback;
    }

    /**
     * @return string|null
     */
    public function getEditUrl(): ?string
    {
        return $this->editUrl;
    }

    /**
     * @return string|null
     */
    public function getAbortUrl(): ?string
    {
        return $this->abortUrl;
    }

    /**
     * @return string|null
     */
    public function getRejectedCallback(): ?string
    {
        return $this->rejectedCallback;
    }

    /**
     * @return string|null
     */
    public function getPartpaymentDetailsGetter(): ?string
    {
        return $this->partpaymentDetailsGetter;
    }

    /**
     * @return string|null
     */
    public function getApprovedUrl(): ?string
    {
        return $this->approvedUrl;
    }

    /**
     * @return Options|null
     */
    public function getOptions(): ?Options
    {
        return $this->options;
    }

    /**
     * @return EventsWebhook|null
     */
    public function getEventsWebhook(): ?EventsWebhook
    {
        return $this->eventsWebhook;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
