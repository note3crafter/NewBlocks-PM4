<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use TheNote\NewBlocks\CustomIds;

class CopperOre extends Opaque
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Copper Ore", $breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE, ));
    }
    public function getDropsForCompatibleTool(Item $item): array
    {
        return [
            ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM)->setCount(mt_rand(1, 3))
        ];
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}
