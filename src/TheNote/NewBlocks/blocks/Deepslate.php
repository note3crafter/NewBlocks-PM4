<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class Deepslate extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Deepslate",$breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
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
        return mt_rand(1, 3);
    }
    public function canBePlaced() : bool{
        return true;
    }

}