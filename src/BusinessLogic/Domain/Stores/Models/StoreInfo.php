<?php

namespace SeQura\Core\BusinessLogic\Domain\Stores\Models;

/**
 * Class StoreInfo
 *
 * @package SeQura\Core\BusinessLogic\Domain\Stores\Models
 */
class StoreInfo
{
    /**
     * @var string
     */
    private $storeName;

    /**
     * @var string
     */
    private $storeUrl;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var string
     */
    private $platformVersion;

    /**
     * @var string
     */
    private $pluginVersion;

    /**
     * @var string
     */
    private $phpVersion;

    /**
     * @var string
     */
    private $db;

    /**
     * @var string
     */
    private $os;

    /**
     * @var string[]
     */
    private $plugins;

    /**
     * @param string $storeName
     * @param string $storeUrl
     * @param string $platform
     * @param string $platformVersion
     * @param string $pluginVersion
     * @param string $phpVersion
     * @param string $db
     * @param string $os
     * @param string[] $plugins
     */
    public function __construct(
        string $storeName,
        string $storeUrl,
        string $platform,
        string $platformVersion,
        string $pluginVersion,
        string $phpVersion,
        string $db,
        string $os,
        array $plugins = []
    ) {
        $this->storeName = $storeName;
        $this->storeUrl = $storeUrl;
        $this->platform = $platform;
        $this->platformVersion = $platformVersion;
        $this->pluginVersion = $pluginVersion;
        $this->phpVersion = $phpVersion;
        $this->db = $db;
        $this->os = $os;
        $this->plugins = $plugins;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * @return string
     */
    public function getStoreUrl(): string
    {
        return $this->storeUrl;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @return string
     */
    public function getPlatformVersion(): string
    {
        return $this->platformVersion;
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @return string[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Adds a plugin to the list.
     *
     * @param string $plugin
     */
    public function addPlugin(string $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Converts the model to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'store_name' => $this->storeName,
            'store_url' => $this->storeUrl,
            'platform' => $this->platform,
            'platform_version' => $this->platformVersion,
            'plugin_version' => $this->pluginVersion,
            'php_version' => $this->phpVersion,
            'db' => $this->db,
            'os' => $this->os,
            'plugins' => $this->plugins,
        ];
    }

    /**
     * Creates a StoreInfo instance from an array.
     *
     * @param mixed[] $data
     *
     * @return StoreInfo
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['store_name'] ?? '',
            $data['store_url'] ?? '',
            $data['platform'] ?? '',
            $data['platform_version'] ?? '',
            $data['plugin_version'] ?? '',
            $data['php_version'] ?? '',
            $data['db'] ?? '',
            $data['os'] ?? '',
            $data['plugins'] ?? []
        );
    }
}
