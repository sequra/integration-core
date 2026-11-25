<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

class ExpressCheckoutForm extends SeQuraForm
{
    private const BASE_URL_PATTERN = '/data-base-url="([^"]*)"/';
    private const ABSOLUTE_URL_PATTERN = '#^(https?://[^/?\#]+)([^?\#]*)(.*)$#is';
    private const ORDER_PAGE_PATTERN = '#(/orders/[^/?\#]+)/[^/?\#]+#';

    /**
     * @var string
     */
    protected $orderUuid;

    public function __construct(string $form, string $orderUuid)
    {
        parent::__construct($form);
        $this->orderUuid = $orderUuid;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getFormUrl(): ?string
    {
        if (!\preg_match(self::BASE_URL_PATTERN, $this->form, $matches)) {
            return null;
        }

        $formUrl = \html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return \preg_match(self::ABSOLUTE_URL_PATTERN, $formUrl) && $this->hasHost($formUrl) ? $formUrl : null;
    }

    public function getMetadataEndpoint(): ?string
    {
        $formUrl = $this->getFormUrl();
        if ($formUrl === null) {
            return null;
        }

        \preg_match(self::ABSOLUTE_URL_PATTERN, $formUrl, $urlParts);
        $metadataPath = \preg_replace(self::ORDER_PAGE_PATTERN, '$1/metadata', $urlParts[2], 1, $replacements);

        return $replacements === 1 ? $urlParts[1] . $metadataPath . $urlParts[3] : null;
    }

    private function hasHost(string $url): bool
    {
        $host = \parse_url($url, PHP_URL_HOST);

        return \is_string($host) && $host !== '';
    }
}
