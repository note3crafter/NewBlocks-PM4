<?php

namespace TheNote\NewBlocks\items;

use pocketmine\item\Releasable;
use pocketmine\item\Sword;
use pocketmine\player\Player;

class NetheriteSword extends Sword implements Releasable
{
    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function canStartUsingItem(Player $player) : bool{
        return true;
    }
}