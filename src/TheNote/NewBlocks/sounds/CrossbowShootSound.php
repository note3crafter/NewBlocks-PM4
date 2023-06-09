<?php

namespace TheNote\NewBlocks\sounds;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\sound\Sound;

final class CrossbowShootSound implements Sound{

    public function encode(Vector3 $pos) : array{
        return [
            LevelSoundEventPacket::nonActorSound(
                LevelSoundEvent::CROSSBOW_SHOOT,
                $pos,
                false
            )
        ];
    }
}