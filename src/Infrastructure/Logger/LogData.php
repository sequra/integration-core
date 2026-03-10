<?php

namespace SeQura\Core\Infrastructure\Logger;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class LogData.
 *
 * @package SeQura\Core\Infrastructure\Logger
 */
class LogData extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Name of the integration.
     *
     * @var string
     */
    protected $integration;
    /**
     * Array of LogContextData.
     *
     * @var LogContextData[]
     */
    protected $context;
    /**
     * Log level.
     *
     * @var int
     */
    protected $logLevel;
    /**
     * Log timestamp.
     *
     * @var int
     */
    protected $timestamp;
    /**
     * Name of the component.
     *
     * @var string
     */
    protected $component;
    /**
     * Log message.
     *
     * @var string
     */
    protected $message;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array('id', 'integration', 'logLevel', 'timestamp', 'component', 'message');

    /**
     * LogData constructor.
     *
     * @param string $integration Name of integration.
     * @param int $logLevel Log level. Use constants in @see Logger class.
     * @param int $timestamp Log timestamp.
     * @param string $component Name of the log component.
     * @param string $message Log message.
     * @param LogContextData[] $context Log contexts as an array of @see LogContextData or as key value entries.
     */
    public function __construct(
        string $integration = '',
        int $logLevel = Logger::ERROR,
        int $timestamp = 0,
        string $component = '',
        string $message = '',
        array $context = []
    ) {
        parent::__construct();

        $this->integration = $integration;
        $this->logLevel = $logLevel;
        $this->component = $component;
        $this->timestamp = $timestamp;
        $this->message = $message;
        $this->context = [];

        foreach ($context as $key => $item) {
            if (!($item instanceof LogContextData)) {
                $item = new LogContextData($key, $item);
            }

            $this->context[] = $item;
        }
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    public function getConfig(): EntityConfiguration
    {
        $map = new IndexMap();
        $map->addStringIndex('integration')
            ->addIntegerIndex('logLevel')
            ->addIntegerIndex('timestamp')
            ->addStringIndex('component');

        return new EntityConfiguration($map, 'LogData');
    }

    /**
     * Transforms raw array data to this entity instance.
     *
     * @param mixed[] $data Raw array data.
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $context = !empty($data['context']) ? $data['context'] : [];
        $this->context = [];
        foreach ($context as $key => $value) {
            $item = new LogContextData($key, $value);
            $this->context[] = $item;
        }
    }

    /**
     * Transforms entity to its array format representation.
     *
     * @return mixed[] Entity in array format.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        foreach ($this->context as $item) {
            $data['context'][$item->getName()] = $item->getValue();
        }

        return $data;
    }

    /**
     * Gets name of the integration.
     *
     * @return string Name of the integration.
     */
    public function getIntegration(): string
    {
        return $this->integration;
    }

    /**
     * Gets context data array.
     *
     * @return LogContextData[] Array of LogContextData.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Gets log level.
     *
     * @return int
     *   Log level:
     *    - error => 0
     *    - warning => 1
     *    - info => 2
     *    - debug => 3
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * Gets timestamp in seconds.
     *
     * @return int Log timestamp.
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Gets log component.
     *
     * @return string Log component.
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Gets log message.
     *
     * @return string Log message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function formatLogMessage(): string
    {
        $dateTime = TimeProvider::getInstance()->getDateTime((int)($this->getTimestamp() / 1000));

        return sprintf(
            "%s\t%s\t%s\t%s\r\n",
            $this->getLevelName(),
            TimeProvider::getInstance()->serializeDate($dateTime, 'Y-m-d H:i:s'),
            $this->getMessage(),
            $this->formatContext()
        );
    }

    /**
     * @return string
     */
    private function formatContext(): string
    {
        if (empty($this->getContext())) {
            return '';
        }

        $ctx = [];
        foreach ($this->getContext() as $logContextData) {
            $arr = $logContextData->toArray();
            if (isset($arr['value']) && is_string($arr['value'])) {
                $decoded = json_decode($arr['value'], true);
                if ($decoded !== null) {
                    $arr['value'] = $decoded;
                }
            }
            $ctx[] = $arr;
        }

        return ' ' . json_encode(
            $ctx,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS
        );
    }

    /**
     * Returns string constant of Log level.
     *
     * @return string
     */
    private function getLevelName(): string
    {
        switch ($this->getLogLevel()) {
            case Logger::DEBUG:
                return 'DEBUG';
            case Logger::INFO:
                return 'INFO';
            case Logger::WARNING:
                return 'WARNING';
            case Logger::ERROR:
                return 'ERROR';
            default:
                return '';
        }
    }
}
