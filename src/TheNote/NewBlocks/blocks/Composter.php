<?php

//   ╔═════╗╔═╗ ╔═╗╔═════╗╔═╗    ╔═╗╔═════╗╔═════╗╔═════╗
//   ╚═╗ ╔═╝║ ║ ║ ║║ ╔═══╝║ ╚═╗  ║ ║║ ╔═╗ ║╚═╗ ╔═╝║ ╔═══╝
//     ║ ║  ║ ╚═╝ ║║ ╚══╗ ║   ╚══╣ ║║ ║ ║ ║  ║ ║  ║ ╚══╗
//     ║ ║  ║ ╔═╗ ║║ ╔══╝ ║ ╠══╗   ║║ ║ ║ ║  ║ ║  ║ ╔══╝
//     ║ ║  ║ ║ ║ ║║ ╚═══╗║ ║  ╚═╗ ║║ ╚═╝ ║  ║ ║  ║ ╚═══╗
//     ╚═╝  ╚═╝ ╚═╝╚═════╝╚═╝    ╚═╝╚═════╝  ╚═╝  ╚═════╝
//   Copyright by TheNote! Not for Resale! Not for others
//

namespace TheNote\NewBlocks\blocks;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use TheNote\NewBlocks\sounds\ComposterEmptySound;
use TheNote\NewBlocks\sounds\ComposterFillSound;
use TheNote\NewBlocks\sounds\ComposterFillSuccessSound;
use TheNote\NewBlocks\sounds\ComposterReadySound;

class Composter extends Opaque
{
    protected $fill = 0;
    protected $ingridients = [
        ItemIds::NETHER_WART => 30,
        ItemIds::GRASS => 30,
        ItemIds::KELP => 30,
        ItemIds::LEAVES => 30,
        ItemIds::DRIED_KELP => 30,
        ItemIds::BEETROOT_SEEDS => 30,
        ItemIds::MELON_SEEDS => 30,
        ItemIds::SEEDS => 30,
        ItemIds::PUMPKIN_SEEDS => 30,
        ItemIds::TALLGRASS => 30,
        ItemIds::SEAGRASS => 30,

        ItemIds::DRIED_KELP_BLOCK => 50,
        ItemIds::CACTUS => 50,
        ItemIds::MELON => 50,
        ItemIds::SUGARCANE => 50,

        ItemIds::MELON_BLOCK => 65,
        ItemIds::MUSHROOM_STEW => 65,
        ItemIds::POTATO => 65,
        ItemIds::WATER_LILY => 65,
        ItemIds::CARROT => 65,
        ItemIds::SEA_PICKLE => 65,
        ItemIds::BROWN_MUSHROOM_BLOCK => 65,
        ItemIds::RED_MUSHROOM_BLOCK => 65,
        ItemIds::WHEAT => 65,
        ItemIds::BEETROOT => 65,
        ItemIds::PUMPKIN => 65,
        ItemIds::CARVED_PUMPKIN => 65,
        ItemIds::RED_FLOWER => 65,
        ItemIds::YELLOW_FLOWER => 65,
        ItemIds::APPLE => 65,

        ItemIds::COOKIE => 85,
        ItemIds::BAKED_POTATO => 85,
        ItemIds::WHEAT_BLOCK => 85,
        ItemIds::BREAD => 85,

        ItemIds::CAKE => 100,
        ItemIds::PUMPKIN_PIE => 100

    ];


    public function __construct(BlockIdentifier $idInfo, ?BlockBreakInfo $breakInfo = null)
    {
        parent::__construct($idInfo, "Composter", $breakInfo ?? new BlockBreakInfo(0.75, BlockToolType::AXE));
    }

    public function getFuelTime(): int
    {
        return 300;
    }

    protected function writeStateToMeta(): int
    {
        return $this->fill;
    }

    public function readStateFromData(int $id, int $stateMeta): void
    {
        $this->fill = BlockDataSerializer::readBoundedInt("fill", $stateMeta, 0, 8);
    }

    public function getStateBitmask(): int
    {
        return 0b1111;
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

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($this->fill >= 8) {
            $this->fill = 0;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
            $this->position->getWorld()->dropItem($this->position->add(0.5, 1.1, 0.5), VanillaItems::BONE_MEAL());
            return true;
        }
        if (isset($this->ingridients[$item->getId()]) && $this->fill < 7) {
            $item->pop();
            $this->spawnParticleEffect($this->position->add(0.5, 0.5, 0.5));
            if ($this->fill == 0) {
                $this->incrimentFill(true);
                return true;
            }
            $chance = $this->ingridients[$item->getId()];
            if (mt_rand(0, 100) <= $chance) {
                $this->incrimentFill(true);
                return true;
            }
            $this->position->getWorld()->addSound($this->position, new ComposterFillSound());
        }
        return true;
    }

    public function incrimentFill(bool $playsound = false): bool
    {
        if ($this->fill >= 7) {
            return false;
        }
        if (++$this->fill >= 7) {
            $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 25);
        } else {
            $this->position->getWorld()->setBlock($this->position, $this);
        }
        if ($playsound) {
            $this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
        }
        return true;
    }

    public function onScheduledUpdate(): void
    {
        if ($this->fill == 7) {
            ++$this->fill;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposterReadySound());
        }
    }
}