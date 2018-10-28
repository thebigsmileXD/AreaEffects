<?php

namespace xenialdan\AreaEffects;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
    private $pos1;
    private $pos2;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command == "ae") {
            switch ($args[0]) {
                case "pos1":
                    {
                        if ($sender instanceof Player) {
                            $pos1x = $sender->getFloorX();
                            $pos1y = $sender->getFloorY();
                            $pos1z = $sender->getFloorZ();
                            $this->pos1 = new Vector3($pos1x, $pos1y, $pos1z);
                            $sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Position 1 set as x:" . $pos1x . " y:" . $pos1y . " z:" . $pos1z);
                            return true;
                        }
                        break;
                    }

                case "pos2":
                    {
                        if ($sender instanceof Player) {
                            $pos2x = $sender->getFloorX();
                            $pos2y = $sender->getFloorY();
                            $pos2z = $sender->getFloorZ();
                            $this->pos2 = new Vector3($pos2x, $pos2y, $pos2z);
                            $sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Position 2 set as x:" . $pos2x . " y:" . $pos2y . " z:" . $pos2z);
                            return true;
                        }
                        break;
                    }

                case "create":
                    {
                        if ($sender instanceof Player) {
                            if (isset($args[1], $args[2])) {
                                if (isset($this->pos1, $this->pos2)) {
                                    if (($id = Main::getInstance()->isEffect($args[2])) !== null) {
                                        Main::getInstance()->getConfig()->setNested($args[1], array('level' => $sender->getLevel()->getName(), 'pos1' => array('x' => $this->pos1->x, 'y' => $this->pos1->y, 'z' => $this->pos1->z), 'pos2' => array('x' => $this->pos2->x, 'y' => $this->pos2->y, 'z' => $this->pos2->z),
                                            'effect' => array('id' => $id, 'duration' => ((isset($args[4]) && is_numeric($args[4])) ? intval($args[4]) : 200), 'amplifier' => ((isset($args[3]) && is_numeric($args[3])) ? intval($args[3]) : 1), 'show' => ((isset($args[5]) && is_bool($args[5])) ? boolval($args[5]) : false))));
                                        Main::getInstance()->saveConfig();
                                        $sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Area created");
                                        return true;
                                    } else {
                                        $sender->sendMessage(TextFormat::RED . "[AreaEffects]Invalid effect id or name given");
                                        return false;
                                    }
                                }
                            } else {
                                $sender->sendMessage(TextFormat::RED . "[AreaEffects]Missing arguments");
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "[AreaEffects]This command must be used in-game");
                        }
                        break;
                    }

                case "remove":
                    {
                        if (isset($args[1])) {
                            Main::getInstance()->getConfig()->remove($args[1]);
                            Main::getInstance()->getConfig()->save();
                            if (!Main::getInstance()->getConfig()->exists($args[1])) {
                                $sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Area removed");
                                return true;
                            } else {
                                $sender->sendMessage(TextFormat::RED . "[AreaEffects]Area removal failed");
                                return true;
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "[AreaEffects]Missing arguments, /ae remove <name>");
                        }
                        break;
                    }

                case "list":
                    {
                        $all = array_keys(Main::getInstance()->getConfig()->getAll());
                        $sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Areas:\n" . TextFormat::GREEN . " - " . TextFormat::AQUA . implode("\n" . TextFormat::GREEN . " - " . TextFormat::AQUA, $all));
                        return true;
                        break;
                    }
                default:
                    {
                        return false;
                    }
            }
            return false;
        }
        return false;
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        foreach (array_keys(Main::getInstance()->getConfig()->getAll()) as $areaname) {
            if ($this->isInArea($player, $areaname)) {
                Main::getInstance()->giveEffect($player, $areaname);
            }
        }
    }

    public function isInArea(Player $player, $areaname)
    {
        if (!Main::getInstance()->getConfig()->exists($areaname)) {
            Main::getInstance()->getConfig()->reload();
            return false;
        }
        $area = Main::getInstance()->getConfig()->getNested($areaname);
        if (($player->getLevel()->getName() == $area['level']) and ($player->getFloorX() <= max($area['pos1']['x'], $area['pos2']['x'])) and ($player->getFloorX() >= min($area['pos1']['x'], $area['pos2']['x'])) and ($player->getFloorY() <= max($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorY() >= min($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorZ() <= max($area['pos1']['z'], $area['pos2']['z'])) and ($player->getFloorZ() >= min($area['pos1']['z'], $area['pos2']['z']))) {
            if (is_null(Main::getInstance()->isEffect($area['effect']['id']))) {
                Main::getInstance()->getConfig()->remove($areaname);
                $message = TextFormat::YELLOW . "[AreaEffects]Invalid effect found, removed area " . TextFormat::GRAY . $areaname;
                foreach (Server::getInstance()->getOps() as $opname) {
                    $op = Server::getInstance()->getPlayer($opname);
                    if ($op instanceof Player && $op->isOnline()) $op->sendMessage($message);
                }
                Main::getInstance()->getLogger()->warning($message);
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

}