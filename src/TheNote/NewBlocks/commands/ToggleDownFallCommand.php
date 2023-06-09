<?php

namespace TheNote\NewBlocks\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use TheNote\NewBlocks\Main;
use TheNote\NewBlocks\world\weather\WeatherManager;

class ToggleDownFallCommand extends Command
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("toggledownfall", "Toggles the weather",  "/toggledownfall");
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
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (($weather = WeatherManager::getInstance()->getWeather($world)) !== null) {
                if ($weather->isRaining()) {
                    $weather->stopStorm();
                    $cfg->set("snow", false);
                    $cfg->save();
                } else {
                    $weather->startStorm();
                    $cfg->set("snow", true);
                    $cfg->save();
                }
            }
        }
        $sender->sendMessage("Toggled downfall");
    }
}