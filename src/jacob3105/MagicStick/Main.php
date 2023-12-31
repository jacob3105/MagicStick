<?php

declare(strict_types=1);

namespace jacob3105\MagicStick;

use customiesdevs\customies\item\CustomiesItemFactory;
use jacob3105\MagicStick\item\MagicStick;
use jacob3105\MagicStick\listeners\EventListener;
use NhanAZ\libBedrock\ResourcePackManager;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {
    use SingletonTrait;

    protected function onEnable(): void {
        self::setInstance($this);
        ResourcePackManager::registerResourcePack($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        CustomiesItemFactory::getInstance()->registerItem(MagicStick::class, "jacob:magic_stick", "Magic Stick");
    }

    protected function onDisable(): void {
        ResourcePackManager::unregisterResourcePack($this);
    }
}