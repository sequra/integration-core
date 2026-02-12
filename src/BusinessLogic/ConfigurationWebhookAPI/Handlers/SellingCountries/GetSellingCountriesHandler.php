<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\SellingCountries;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SellingCountries\SellingCountriesResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\SellingCountries\GetSellingCountriesRequest;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;

/**
 * Class GetSellingCountriesHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetSellingCountriesHandler implements TopicHandlerInterface
{
    /**
     * @var SellingCountriesServiceInterface $sellingCountriesService
     */
    protected $sellingCountriesService;

    /**
     * @param SellingCountriesServiceInterface $sellingCountriesService
     */
    public function __construct(SellingCountriesServiceInterface $sellingCountriesService)
    {
        $this->sellingCountriesService = $sellingCountriesService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $request = GetSellingCountriesRequest::fromPayload($payload);

        return new SellingCountriesResponse($this->sellingCountriesService->getSellingCountries(
            $request->getPage(),
            $request->getLimit(),
            $request->getSearch()
        ));
    }
}
