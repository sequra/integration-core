<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\SellingCountries;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SellingCountries\SellingCountriesResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\SellingCountries\GetSellingCountriesRequest;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;

/**
 * Class GetSellingCountriesHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetSellingCountriesHandler implements TopicHandlerInterface
{
    /**
     * @var SellingCountriesService $sellingCountriesService
     */
    protected $sellingCountriesService;

    /**
     * @param SellingCountriesService $sellingCountriesService
     */
    public function __construct(SellingCountriesService $sellingCountriesService)
    {
        $this->sellingCountriesService = $sellingCountriesService;
    }

    /**
     * @inheritDoc
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function handle(array $payload): Response
    {
        $request = GetSellingCountriesRequest::fromPayload($payload);

        $items = $this->sellingCountriesService->getSellingCountries();
        $page = max(1, $request->getPage());
        $limit = max(1, $request->getLimit());
        $search = $request->getSearch();

        if ($search !== '') {
            $items = array_filter($items, function ($item) use ($search) {
                return stripos($item->getName(), $search) !== false;
            });
        }
        $items = array_values($items);
        $paginatedItems = array_slice($items, ($page - 1) * $limit, $limit);

        $data = array_map(function ($item) {
            return $item->getCode();
        }, $paginatedItems);

        return new SellingCountriesResponse($data);
    }
}
