<?php
declare(strict_types = 1);

namespace JackMD\KDR;

use JackMD\KDR\provider\ProviderInterface;
use JackMD\KDR\provider\SQLiteProvider;
use JackMD\KDR\provider\YamlProvider;
use pocketmine\plugin\PluginBase;

class KDR extends PluginBase {
    
    /** @var KDR */
    private static $instance;
    
    /** @var ProviderInterface|null */
    private $provider;
    
    /**
     * @return KDR
     */
    public static function getInstance(): KDR {
        return self::$instance;
    }
    
    public function onLoad(): void {
        self::$instance = $this;
    }
    
    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->setProvider();
        $this->getProvider()->prepare();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("KillCounter Plugin Enabled.");
    }
    
    /**
     * @return bool
     */
    private function isValidProvider(): bool {
        if (!isset($this->provider) || ($this->provider === null) || !($this->provider instanceof ProviderInterface)) {
            return false;
        }
        return true;
    }
    
    public function onDisable(): void {
        if ($this->isValidProvider()) {
            $this->getProvider()->close();
        }
    }
    
    /**
     * @return ProviderInterface|null
     */
    public function getProvider(): ?ProviderInterface {
        return $this->provider;
    }
    
    private function setProvider(): void {
        $providerName = $this->getConfig()->get("data-provider");
        $provider = null;
        switch (strtolower($providerName)) {
            case "sqlite":
                $provider = new SQLiteProvider();
                $this->getLogger()->notice("SQLiteProvider successfully enabled.");
                break;
            case "yaml":
                $provider = new YamlProvider();
                $this->getLogger()->notice("YamlProvider successfully enabled.");
                break;
            default:
                $this->getLogger()->error("Please set a valid data-provider in config.yml. Plugin Disabled");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                break;
        }
        if ($provider instanceof ProviderInterface) {
            $this->provider = $provider;
        }
    }
}
