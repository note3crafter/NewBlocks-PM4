<?php

namespace TheNote\NewBlocks\world;

use TheNote\NewBlocks\world\gamerule\GameRuleManager;
use pocketmine\utils\SingletonTrait;
use TheNote\NewBlocks\world\weather\WeatherManager;

class WorldManager{
    use SingletonTrait;

    private WeatherManager $weatherManager;
    private GameRuleManager $gameruleManager;

    public function __construct(){
        self::setInstance($this);
        $this->weatherManager = new WeatherManager();
        $this->gameruleManager = new GameRuleManager();
    }

    public function startup(): void{
        $this->weatherManager->startup();
        $this->gameruleManager->startup();
    }

    public function getWeatherManager(): WeatherManager{
        return $this->weatherManager;
    }

    public function getGameruleManager(): GameRuleManager{
        return $this->gameruleManager;
    }
}