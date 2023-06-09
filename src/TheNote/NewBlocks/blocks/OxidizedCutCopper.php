<?php

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use TheNote\NewBlocks\CustomIds;
use TheNote\NewBlocks\sounds\CopperWaxOnSound;

class OxidizedCutCopper extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Oxidized Cut Copper",$breakInfo ?? new BlockBreakInfo(3, BlockToolType::PICKAXE));
    }

    public function canBePlaced() : bool{
        return true;
    }
    private function spawnParticleEffect(Vector3 $position): void
    {
        $packet = new SpawnParticleEffectPacket();
        $packet->position = $position;
        $packet->particleName = "minecraft:crop_growth_emitter";
        $recipients = $this->position->getWorld()->getViewersForPosition($this->position);
        foreach($recipients as $player){
            $player->getNetworkSession()->sendDataPacket($packet);
        }
    }
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        $honey = ItemFactory::getInstance()->get(CustomIds::HONEYCOMB);
        if($item instanceof $honey) {
            if ($player->getInventory()->getItemInHand()->getId() == 736) {
                $this->position->getWorld()->setBlock($this->position, BlockFactory::getInstance()->get(CustomIds::WAXED_OXIDIZED_CUT_COPPER, 0));
                $player->getInventory()->removeItem(ItemFactory::getInstance()->get(CustomIds::HONEYCOMB, 0, 1));
                $this->spawnParticleEffect($this->position->add(0.5, 1.1, 0.5));
                $this->position->getWorld()->addSound($this->position, new CopperWaxOnSound());
                return true;
            }
        }
        return false;
    }
}