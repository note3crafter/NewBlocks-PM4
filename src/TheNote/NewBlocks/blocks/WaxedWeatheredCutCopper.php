<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Axe;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use TheNote\NewBlocks\CustomIds;
use TheNote\NewBlocks\sounds\CopperWaxOffSound;

class WaxedWeatheredCutCopper extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Waxed Weathered Cut Copper",$breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }

    public function canBePlaced() : bool{
        return true;
    }
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if($item instanceof Axe){
            $item->applyDamage(1);
            $this->position->getWorld()->setBlock($this->position, BlockFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER,0));
            $this->position->getWorld()->addSound($this->position, new CopperWaxOffSound());
            return true;
        }
        return false;
    }
}