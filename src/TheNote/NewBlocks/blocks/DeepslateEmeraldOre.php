<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;

class DeepslateEmeraldOre extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Deepslate Emerald Ore", $breakInfo ?? new BlockBreakInfo(4.5, BlockToolType::PICKAXE, ToolTier::IRON(), 3));
    }
    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            VanillaItems::EMERALD()
        ];
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}