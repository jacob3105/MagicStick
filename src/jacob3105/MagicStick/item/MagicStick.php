<?php

declare(strict_types=1);

namespace jacob3105\MagicStick\item;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use jacob3105\MagicStick\Main;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Sapling;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Random;
use pocketmine\world\generator\object\TreeFactory;
use pocketmine\world\generator\object\TreeType;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\World;

class MagicStick extends Item implements ItemComponents {
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier, string $name = "Unknown") {
        parent::__construct($identifier, $name);
        $this->initComponent("magic_stick", new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_WOOD));
    }

    public function getMaxStackSize(): int {
        return 1;
    }

    public function getSeed(): ?int {
        if (($seed = $this->getNamedTag()->getTag("seeds")) === null) return null;
        return $seed->getValue();
    }

    final public function setSeed(Sapling $sapling): void {
        $this->getNamedTag()->setInt("seeds", $sapling->getStateId());
        $this->setLore([
            "§r§7Seed: §r§f" . $sapling->getName()
        ]);
    }

    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult {
        $seed = $this->getSeed();
        if ($seed === null) {
            $player->sendMessage("You have not selected a seed yet!");
            return ItemUseResult::FAIL();
        }
        $positionPlant = $player->getEyePos()->addVector($player->getDirectionVector()->multiply(3));
        $this->drawHeart($player->getWorld(), $positionPlant->x, $positionPlant->y, $positionPlant->z);
        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $positionPlant): void {
            $this->plantTree($player->getWorld(), $positionPlant);
        }), 25);
        return ItemUseResult::SUCCESS();
    }

    private function plantTree(World $world, Vector3 $position): void {
        $seed = $this->getSeed();
        $sapling = RuntimeBlockStateRegistry::getInstance()->fromStateId($seed);
        $blockFromPosition = $world->getBlock($position);
        $world->setBlock($position, $sapling);
        $random = new Random(mt_rand());
        $treeType = TreeType::OAK();
        switch ($sapling->getTypeId()) {
            case BlockTypeIds::SPRUCE_SAPLING:
                $treeType = TreeType::SPRUCE();
                break;
            case BlockTypeIds::BIRCH_SAPLING:
                $treeType = TreeType::BIRCH();
                break;
            case BlockTypeIds::JUNGLE_SAPLING:
                $treeType = TreeType::JUNGLE();
                break;
            case BlockTypeIds::ACACIA_SAPLING:
                $treeType = TreeType::ACACIA();
                break;
            case BlockTypeIds::DARK_OAK_SAPLING:
                $treeType = TreeType::DARK_OAK();
                break;
        }
        $tree = TreeFactory::get($random, $treeType);
        $transaction = $tree?->getBlockTransaction($world, $position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $random);
        if($transaction === null) return;

        $ev = new StructureGrowEvent($blockFromPosition, $transaction, null);
        $ev->call();
        if(!$ev->isCancelled()){
            $transaction->apply();
        }
    }

    private function drawHeart(World $world, float $x, float $y, float $z): void {
        $heartPoints = [
            new Vector3($x + 1.5, $y, $z),
            new Vector3($x + 0.5, $y, $z - 1),
            new Vector3($x + 0.5, $y, $z + 1),
            new Vector3($x - 1.5, $y, $z),
            new Vector3($x - 0.5, $y, $z - 1),
            new Vector3($x - 0.5, $y, $z + 1),
        ];

        foreach ($heartPoints as $point) {
            $particle = new HeartParticle(2);
            $world->addParticle($point, $particle);
        }
    }
}