<?php

namespace Azzam\InvisiblePlayers;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
    private $invisiblePlayers = [];
    public $world;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource('config.yml');
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $this->world = $this->config->get("World");
        $this->getServer()->getWorldManager()->loadWorld($this->world);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "invisible") {
            if ($sender instanceof Player && $sender->getPosition()->getWorld()->getFolderName() === $this->world) {
                if (!isset($this->invisiblePlayers[$sender->getName()])) {
                    foreach ($this->getServer()->getWorldManager()->getWorldByName($this->world)->getPlayers() as $player){
                        $sender->hidePlayer($player);
                    }

                    $this->invisiblePlayers[$sender->getName()] = $sender;

                    $sender->sendMessage(TextFormat::GREEN . "Tous les joueurs sont maintenant invisibles pour vous dans le monde '".$this->world."'.");
                } else {
                    foreach ($this->getServer()->getWorldManager()->getWorldByName("EventA")->getPlayers() as $player){
                        $sender->showPlayer($player);
                    }

                    unset($this->invisiblePlayers[$sender->getName()]);
                    $sender->sendMessage(TextFormat::RED . "Tous les joueurs ne sont plus invisibles pour vous dans le monde '".$this->world."'.");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "Cette commande ne peut être exécutée que par un joueur en jeu dans le monde 'event'.");
            }
        }

        return true;
    }

    public function onEntityTeleport(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $fromWorld = $event->getFrom()->getWorld();
            $toWorld = $event->getTo()->getWorld();
            if ($fromWorld->getDisplayName() !== $toWorld->getDisplayName()) {
                if ($toWorld->getFolderName() === $this->world) {
                    foreach ($this->invisiblePlayers as $invisible) {
                        $invisible->hidePlayer($player);
                    }
                } else {
                    foreach ($toWorld->getPlayers() as $worldPlayer) {
                        $player->showPlayer($worldPlayer);
                        $worldPlayer->showPlayer($player);
                    }

                    if (isset($this->invisiblePlayers[$player->getName()])) {
                        unset($this->invisiblePlayers[$player->getName()]);
                    }
                }
            }
        }
    }
}