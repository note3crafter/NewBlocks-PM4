<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use TheNote\NewBlocks\CustomIds;

class GoldOre extends Opaque
{
    public function getDropsForCompatibleTool(Item $item) : array{
        $item->setCount(1);

        return [
            ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_ITEM)
        ];
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}