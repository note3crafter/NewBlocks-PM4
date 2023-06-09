<?php

namespace TheNote\NewBlocks\world\weather;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use TheNote\NewBlocks\entity\object\LightningBoltEntity;
use TheNote\NewBlocks\Main;
use TheNote\NewBlocks\world\gamerule\GameRule;
use TheNote\NewBlocks\world\gamerule\GameRuleManager;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class WeatherManager
{
    use SingletonTrait;

    public $cooltime = 0;
    private array $weathers = [];

    public function __construct()
    {
        self::setInstance($this);

    }

    public function startup(): void
    {

        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (!GameRuleManager::getInstance()->getValue(GameRule::DO_WEATHER_CYCLE, $world)) {
                continue;
            }
            $this->addWeather($world);
        }
        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->weathers as $weather) {
                if ($weather->isRaining()) {
                    $cfg = new Config(Main::getInstance()->getDataFolder() . "task.json", Config::JSON);
                    if ($cfg->get("snow") === true) {
                        $this->SnowMod();
                    }
                    $weather->duration--;
                    if ($weather->duration < 1) {
                        $weather->stopStorm();
                    } elseif ($weather->isThundering() && mt_rand(0, 5000) === 0) { //100,000(chance of lightning striking)/20(1s tick) = 5,000 (per sec chance)
                        $players = Server::getInstance()->getOnlinePlayers();
                        if (count($players) >= 1) {
                            $random = $players[array_rand($players)];
                            $location = $random->getLocation();
                            $location->x += mt_rand(0, 15);
                            $location->y += mt_rand(0, 15);
                            $entity = new LightningBoltEntity($location);
                            $entity->spawnToAll();
                        }
                    }
                } else {
                    $weather->delayDuration--;

                    if ($weather->delayDuration < 1) {
                        $weather->startStorm();
                    }
                }

                $weather->saveData();
            }
        }), 20);
    }

    public function addWeather(World $world): void
    {
        $this->weathers[strtolower($world->getFolderName())] = new Weather($world);
    }

    public function removeWeather(World $world): void
    {
        if (isset($this->weathers[strtolower($world->getFolderName())])) {
            unset($this->weathers[strtolower($world->getFolderName())]);
        }
    }

    public function getWeather(World|string $world): ?Weather
    {
        $worldName = $world;

        if ($world instanceof World) {
            $worldName = $world->getFolderName();

            if (!isset($this->weathers[strtolower($worldName)])) {
                $this->addWeather($world);
            }
        }
        return $this->weathers[strtolower($worldName)] ?? null;
    }

    public function isRaining(World $world, bool $checkThunder = true): bool
    {
        $weather = $this->weathers[strtolower($world->getFolderName())] ?? null;

        if ($weather !== null) {
            return $weather->isRaining() || (($checkThunder && $weather->isThundering()));
        }
        return false;
    }

    public function isThundering(World $world): bool
    {
        $weather = $this->weathers[strtolower($world->getFolderName())] ?? null;

        if ($weather !== null && $weather->isThundering()) {
            return true;
        }
        return false;
    }

    public function sendClear(null|Player|array $player = null, bool $thunder = false): void
    {
        if ($player === null) {
            $player = Server::getInstance()->getOnlinePlayers();
        } elseif ($player instanceof Player) {
            $player = [$player];
        }
        foreach ($player as $p) {
            $pk = new LevelEventPacket();
            $pk->eventId = LevelEvent::STOP_RAIN;
            $pk->eventData = 0;
            $pk->position = new Vector3(0, 0, 0);
            $p->getNetworkSession()->sendDataPacket($pk);
            if ($thunder) {
                $pk = new LevelEventPacket();
                $pk->eventData = LevelEvent::STOP_THUNDER;
                $pk->eventId = 0;
                $pk->position = new Vector3(0, 0, 0);
                $p->getNetworkSession()->sendDataPacket($pk);
            }
            $cfg = new Config(Main::getInstance()->getDataFolder() . "task.json", Config::JSON);
            $cfg->set("snow", false);
            $cfg->save();
        }
    }

    public function sendWeather(Player|array $player = null, bool $thunder = false): void
    {
        if ($player === null) {
            $player = Server::getInstance()->getOnlinePlayers();
        } elseif ($player instanceof Player) {
            $player = [$player];
        }
        foreach ($player as $p) {
            $pk = new LevelEventPacket();
            $pk->eventId = LevelEvent::START_RAIN;
            $pk->eventData = 65535;
            $pk->position = new Vector3(0, 0, 0);
            $p->getNetworkSession()->sendDataPacket($pk);

            if ($thunder) {
                $pk = new LevelEventPacket();
                $pk->eventId = LevelEvent::START_THUNDER;
                $pk->eventData = 65535;
                $pk->position = new Vector3(0, 0, 0);
                $p->getNetworkSession()->sendDataPacket($pk);
            }
            $cfg = new Config(Main::getInstance()->getDataFolder() . "task.json", Config::JSON);
            $cfg->set("snow", true);
            $cfg->save();
        }
    }

    public function SnowMod(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->SnowModCreate($player);
        }
    }

    public function SnowModCreate(Player $player): void
    {
        $x = mt_rand(intval($player->getPosition()->getX()) - 15, intval($player->getPosition()->getX()) + 15);
        $z = mt_rand(intval($player->getPosition()->getZ()) - 15, intval($player->getPosition()->getZ()) + 15);
        $y = $player->getWorld()->getHighestBlockAt($x, $z);

        if ($this->cooltime < 11) {
            $this->cooltime++;
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new SnowCreateLayer ($this, new Position ($x, $y, $z, $player->getWorld())), 20);
        }
    }

    public function SnowCreate(Vector3 $pos): void
    {
        $this->cooltime--;
        if ($pos == null)
            return;
        $down = $pos->getWorld()->getBlock($pos);
        if (!$down->isSolid())
            return;
        if ($down->getId() == BlockLegacyIds::MYCELIUM
            or $down->getId() == BlockLegacyIds::PODZOL
            or $down->getId() == BlockLegacyIds::COBBLESTONE
            or $down->getId() == BlockLegacyIds::DEAD_BUSH
            or $down->getId() == BlockLegacyIds::WATER
            or $down->getId() == BlockLegacyIds::LAVA
            or $down->getId() == BlockLegacyIds::WOOL
            or $down->getId() == BlockLegacyIds::ACACIA_FENCE_GATE
            or $down->getId() == BlockLegacyIds::BIRCH_FENCE_GATE
            or $down->getId() == BlockLegacyIds::DARK_OAK_FENCE_GATE
            or $down->getId() == BlockLegacyIds::JUNGLE_FENCE_GATE
            or $down->getId() == BlockLegacyIds::OAK_FENCE_GATE
            or $down->getId() == BlockLegacyIds::SPRUCE_FENCE_GATE
            or $down->getID() == BlockLegacyIds::SMOOTH_STONE
            or $down->getId() == BlockLegacyIds::FARMLAND)
            return;

        $up = $pos->getWorld()->getBlock($pos->add(0, 1, 0));
        if ($up->getId() != BlockLegacyIds::AIR)
            return;
        $settings = new Config(Main::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
        $level = $settings->get("worlds", []);
        $wname = $pos->getWorld()->getFolderName();
        if (in_array($wname, $level)) {
            if ($pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::ICE_PLAINS
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::ICE_PLAINS_SPIKES
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::ICE_MOUNTAINS
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::COLD_BEACH
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::COLD_OCEAN
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::COLD_TAIGA
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::COLD_TAIGA_HILLS
                or $pos->getWorld()->getBiomeId($pos->x, $pos->z) === BiomeIds::COLD_TAIGA_MUTATED) {
                $pos->getWorld()->setBlock($pos->add(0, 1, 0), BlockFactory::getInstance()->get(BlockLegacyIds::SNOW_LAYER, 0), true);
            }
        }
    }
}