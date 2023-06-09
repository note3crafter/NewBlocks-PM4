<?php

namespace TheNote\NewBlocks\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use TheNote\NewBlocks\Main;
use TheNote\NewBlocks\world\weather\WeatherManager;

class WeatherCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("weather", "change weather", "/weather", ["clear", "rain", "thunder"]);
        $this->setPermission("core.command.weather");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $cfg = new Config(Main::getInstance()->getDataFolder() . "task.json" , Config::JSON);
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis Command can only run InGame");
            return;
        }
        if (!$this->testPermission($sender)) {
            $sender->sendMessage("§cYou have no Permission to do that!");
            return;
        }
        $duration = 6000;

        $weathers = [];
        if (!$sender instanceof Player) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                if (($weather = WeatherManager::getInstance()->getWeather($world)) !== null) {
                    $weathers[] = $weather;
                }
            }
        } else {
            $weathers[] = WeatherManager::getInstance()->getWeather($sender->getWorld());
        }

        if (isset($args[1]) && is_numeric($args[1])) {
            $duration = intval($args[1]);
        }
        switch ($type = strtolower($args[0])) {
            case "clear":
                foreach ($weathers as $weather) $weather->stopStorm();
                $sender->sendMessage("Changing to clear weather");
                break;
            case "query":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "This command is only available in game.");
                    return;
                }
                $state = "clear";
                $cfg->set("snow", false);
                $cfg->save();
                $weather = WeatherManager::getInstance()->getWeather($sender->getWorld());
                if ($weather->isRaining()) {
                    if ($weather->isThundering()) {
                        $state = "thunder";
                    } else {
                        $state = "rain";
                    }
                }
                $this->plugin->getScheduler()->cancelAllTasks();
                $sender->sendMessage("Weather state is: " . $state);
                return;
            case "rain":
                foreach ($weathers as $weather) $weather->startStorm(false, $duration);
                $sender->sendMessage("Changing to rainy weather");
                $cfg->set("snow", true);
                $cfg->save();
                return;
            case "thunder":
                foreach ($weathers as $weather) $weather->startStorm(true, $duration);
                $sender->sendMessage("Changing to rain and thunder");
                $cfg->set("snow", true);
                $cfg->save();
                return;
            default:
                $sender->sendMessage("/weather (clear rain thunder)");
        }
    }
}