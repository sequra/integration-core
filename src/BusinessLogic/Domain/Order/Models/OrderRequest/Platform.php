<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class Platform
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Platform extends OrderRequestDTO
{
    /**
     * @var string Name of the platform.
     */
    protected $name;

    /**
     * @var string Version of the platform.
     */
    protected $version;

    /**
     * @var string|null Version of the plugin or platform module.
     */
    protected $pluginVersion;

    /**
     * @var string uname of the shop server.
     */
    protected $uname;

    /**
     * @var string DB used.
     */
    protected $dbName;

    /**
     * @var string Version of the DB.
     */
    protected $dbVersion;

    /**
     * @var string|null PHP interpreter version.
     */
    protected $phpVersion;

    /**
     * @param string $name
     * @param string $version
     * @param string|null $pluginVersion
     * @param string $uname
     * @param string $dbName
     * @param string $dbVersion
     * @param string|null $phpVersion
     */
    public function __construct(
        string $name,
        string $version,
        string $uname,
        string $dbName,
        string $dbVersion,
        string $pluginVersion = null,
        string $phpVersion = null
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->uname = $uname;
        $this->dbName = $dbName;
        $this->dbVersion = $dbVersion;
        $this->pluginVersion = $pluginVersion;
        $this->phpVersion = $phpVersion;
    }

    /**
     * Create a new Platform instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Platform Returns a new Platform instance.
     */
    public static function fromArray(array $data): Platform
    {
        return new self(
            self::getDataValue($data, 'name'),
            self::getDataValue($data, 'version'),
            self::getDataValue($data, 'uname'),
            self::getDataValue($data, 'db_name'),
            self::getDataValue($data, 'db_version'),
            self::getDataValue($data, 'plugin_version', null),
            self::getDataValue($data, 'php_version', null)
        );
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getPluginVersion(): ?string
    {
        return $this->pluginVersion;
    }

    /**
     * @return string
     */
    public function getUname(): string
    {
        return $this->uname;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getDbVersion(): string
    {
        return $this->dbVersion;
    }

    /**
     * @return string|null
     */
    public function getPhpVersion(): ?string
    {
        return $this->phpVersion;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
