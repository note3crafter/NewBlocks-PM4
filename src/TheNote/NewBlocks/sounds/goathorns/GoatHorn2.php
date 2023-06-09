<?php

namespace TheNote\NewBlocks\sounds\goathorns;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\world\sound\Sound;
use TheNote\NewBLOCKs\CustomIds;

class GoatHorn2 implements Sound{

    public function encode(Vector3 $pos) : array{
        return [LevelSoundEventPacket::nonActorSound(CustomIds::GOAT_HORN_2, $pos, false)];
    }
}