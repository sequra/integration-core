<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class OrderReportTask
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks
 */
class OrderReportTask extends Task
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string[]
     */
    protected $reportOrderIds;

    /**
     * @var string[] | null
     */
    protected $statisticsOrderIds;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * OrderReportTask constructor.
     *
     * @param string $merchantId
     * @param string[] $reportOrderIds
     * @param string[]|null $statisticsOrderIds
     */
    public function __construct(string $merchantId, array $reportOrderIds, ?array $statisticsOrderIds = null)
    {
        $this->merchantId = $merchantId;
        $this->reportOrderIds = $reportOrderIds;
        $this->statisticsOrderIds = $statisticsOrderIds;
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * Transforms array into a serializable object,
     *
     * @param array $array
     *
     * @return Serializable
     *
     * @throws Exception
     */
    public static function fromArray(array $array): Serializable
    {
        return StoreContext::doWithStore($array['storeId'], static function () use ($array) {
            return new static($array['merchantId'], $array['reportOrderIds'], $array['statisticsOrderIds'] ?? null);
        });
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'reportOrderIds' => $this->reportOrderIds,
            'statisticsOrderIds' => $this->statisticsOrderIds,
            'merchantId' => $this->merchantId
        ];
    }

    /**
     * @inheritDocs
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDocs
     */
    public function __unserialize($data): void
    {
        $this->merchantId = $data['merchantId'];
        $this->reportOrderIds = $data['reportOrderIds'];
        $this->statisticsOrderIds = $data['statisticsOrderIds'] ?? null;
        $this->storeId = $data['storeId'];
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return Serializer::serialize(
            array($this->merchantId, $this->reportOrderIds, $this->statisticsOrderIds, $this->storeId)
        );
    }

    /**
     * Constructs the object
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        [$this->merchantId, $this->reportOrderIds, $this->statisticsOrderIds, $this->storeId] = Serializer::unserialize(
            $serialized
        );
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore(
            $this->storeId,
            function () {
                $this->doExecute();
            }
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidUrlException
     */
    protected function doExecute(): void
    {
        $this->getOrderReportService()->sendReport(
            new ReportData($this->merchantId, $this->reportOrderIds, $this->statisticsOrderIds)
        );

        $this->reportProgress(100);
    }

    /**
     * Returns an instance of the OrderReportService.
     *
     * @return OrderReportService
     */
    protected function getOrderReportService(): OrderReportService
    {
        return ServiceRegister::getService(OrderReportService::class);
    }
}
