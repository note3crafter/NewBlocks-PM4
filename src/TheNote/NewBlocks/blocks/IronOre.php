<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use TheNote\NewBlocks\CustomIds;

class IronOre extends Opaque
{
    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            ItemFactory::getInstance()->get(CustomIds::RAW_IRON_ITEM)
        ];
    }
    public function canBePlaced(): bool
    {
        return true;
    }
}