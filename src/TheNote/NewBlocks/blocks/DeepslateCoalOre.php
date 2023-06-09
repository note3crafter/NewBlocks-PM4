<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;

class DeepslateCoalOre extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Deepslate Coal Ore",$breakInfo ?? new BlockBreakInfo(4.5, BlockToolType::PICKAXE, ToolTier::WOOD(), 3));
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [
            VanillaItems::COAL()
        ];
    }
    public function isAffectedBySilkTouch(): bool
    {
        return true;
    }
    protected function getXpDropAmount(): int
    {
        return mt_rand(0, 2);
    }
    public function canBePlaced() : bool{
        return true;
    }

}