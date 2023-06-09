<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class DeepslateRedstoneOre extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Deepslate Redstone Ore", $breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }
    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            VanillaItems::REDSTONE_DUST()->setCount(mt_rand(4, 5))
        ];
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}