<?php

namespace SeQura\Core\BusinessLogic\DataAccess\SendReport\Entities;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport as DomainSendReport;

/**
 * Class SendReport
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\SendReport\Entities
 */
class SendReport extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var int
     */
    protected $sendReportTime;

    /**
     * @var DomainSendReport
     */
    protected $sendReport;

    /**
     * @var string[]
     */
    protected $fields = [
        'id',
        'context',
        'sendReportTime'
    ];

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->context = $data['context'];
        $this->sendReportTime = $data['sendReportTime'];
        $sendData = $data['sendData'] ?? [];


        $this->sendReport = new DomainSendReport(self::getArrayValue($sendData, 'sendReportTime'));
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['context'] = $this->context;
        $data['sendReportTime'] = $this->sendReportTime;
        $data['sendData'] = ['sendReportTime' => $this->sendReport->getSendReportTime()];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('context');
        $indexMap->addIntegerIndex('sendReportTime');

        return new EntityConfiguration($indexMap, 'SendReport');
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    /**
     * @return DomainSendReport
     */
    public function getSendReport(): DomainSendReport
    {
        return $this->sendReport;
    }

    /**
     * @param DomainSendReport $sendReport
     *
     * @return void
     */
    public function setSendReport(DomainSendReport $sendReport): void
    {
        $this->sendReport = $sendReport;
    }

    /**
     * @return int
     */
    public function getSendReportTime(): int
    {
        return $this->sendReportTime;
    }

    /**
     * @param int $sendReportTime
     *
     * @return void
     */
    public function setSendReportTime(int $sendReportTime): void
    {
        $this->sendReportTime = $sendReportTime;
    }
}
