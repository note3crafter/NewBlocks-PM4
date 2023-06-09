<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Slab;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SlabType;

class PolishedDeepslateSlab extends Slab
{
    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Polished Deepslate Slab", $breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }
    use HorizontalFacingTrait;

    public function writeStateToMeta() : int{
        if(!$this->slabType->equals(SlabType::DOUBLE())){
            return ($this->slabType->equals(SlabType::TOP()) ? 1 : 0);
        }
        return 0;
    }

    public function readStateFromData(int $id, int $stateMeta) : void{
        if($id === $this->idInfoFlattened->getSecondId()){
            $this->slabType = SlabType::DOUBLE();
        }else{
            $this->slabType = ($stateMeta === 1 ? SlabType::TOP() : SlabType::BOTTOM());
        }
    }

    public function canBePlaced(): bool
    {
        return true;
    }
}