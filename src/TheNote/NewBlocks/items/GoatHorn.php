<?php

namespace TheNote\NewBlocks\items;

use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn0;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn1;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn2;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn3;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn4;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn5;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn6;
use TheNote\NewBlocks\sounds\goathorns\GoatHorn7;


class GoatHorn extends Item{

    public function getMaxStackSize() : int{
        return 1;
    }

    public function getCooldownTicks() : int{
        return 60;
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
        if($player->getInventory()->getItemInHand()->getMeta() == 0){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn0());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 1){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn1());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 2){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn2());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 3){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn3());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 4){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn4());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 5){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn5());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 6){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn6());
        }
        if($player->getInventory()->getItemInHand()->getMeta() == 7){
            $player->getWorld()->addSound($player->getPosition()->add(0.5, 0.5, 0.5), new GoatHorn7());
        }
        return ItemUseResult::SUCCESS();
    }
}