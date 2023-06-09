<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Stair;

class PolishedDeepslateStairs extends Stair
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Polished Deepslate Stairs", $breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}