<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use TheNote\NewBlocks\CustomIds;

class DeepslateIronOre extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Deepslate Iron Ore", $breakInfo ?? new BlockBreakInfo(4.5, BlockToolType::PICKAXE, ToolTier::STONE(), 3));
    }
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