<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;

class YellowCandle extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Yellow Candle", $breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}