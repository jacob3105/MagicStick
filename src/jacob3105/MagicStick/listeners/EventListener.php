<?php

declare(strict_types=1);

namespace jacob3105\MagicStick\listeners;

use jacob3105\MagicStick\item\MagicStick;
use pocketmine\block\Sapling;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\ItemBlock;

class EventListener implements Listener {

    public function onInteract(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2){
            foreach ($actions as $i => $action) {
                if (
                    $action instanceof SlotChangeAction &&
                    ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction &&
                    ($itemClickedWith = $action->getTargetItem()) instanceof ItemBlock &&
                    $itemClickedWith->getBlock() instanceof Sapling &&
                    ($itemClicked = $action->getSourceItem()) instanceof MagicStick
                ) {
                    $change = false;
                    $sapling = $itemClickedWith->getBlock();
                    if ($itemClicked->getSeed() !== $sapling->getTypeId()) {
                        $itemClicked->setSeed($itemClickedWith->getBlock());
                        $change = true;
                    }
                    if ($change) {
                        $event->cancel();
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), $itemClickedWith->setCount($itemClickedWith->getCount() - 1));
                    }
                }
            }
        }
    }
}