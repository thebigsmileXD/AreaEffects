<?php

namespace AreaEffects;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
	public $areas;
	private $pos1, $pos2;

	public function onLoad(){
		$this->getLogger()->info(TextFormat::GREEN . "AreaEffects has been loaded!");
	}

	public function onEnable(){
		$this->saveResource("config.yml");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(TextFormat::GREEN . "AreaEffects enabled!");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if($command == "ae"){
			switch($args[0]){
				case "pos1":
					{
						if($sender instanceof Player){
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
						if($sender instanceof Player){
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
						if($sender instanceof Player){
							if(isset($args[1], $args[2])){
								if(isset($this->pos1, $this->pos2)){
									if(($id = $this->isEffect($args[2])) !== null){
										$this->getConfig()->setNested($args[1], array('level' => $sender->getLevel()->getName(),'pos1' => array('x' => $this->pos1->x,'y' => $this->pos1->y,'z' => $this->pos1->z),'pos2' => array('x' => $this->pos2->x,'y' => $this->pos2->y,'z' => $this->pos2->z),
												'effect' => array('id' => $id,'duration' => ((isset($args[4]) && is_numeric($args[4]))?intval($args[4]):200),'amplifier' => ((isset($args[3]) && is_numeric($args[3]))?intval($args[3]):1),'show' => ((isset($args[5]) && is_bool($args[5]))?boolval($args[5]):false))));
										$this->saveConfig();
										$sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Area created");
										return true;
									}
									else{
										$sender->sendMessage(TextFormat::RED . "[AreaEffects]Invalid effect id or name given");
										return false;
									}
								}
							}
							else{
								$sender->sendMessage(TextFormat::RED . "[AreaEffects]Missing arguments");
							}
						}
						else{
							$sender->sendMessage(TextFormat::RED . "[AreaEffects]This command must be used in-game");
						}
						break;
					}
				
				case "remove":
					{
						if(isset($args[1])){
							$this->getConfig()->remove($args[1]);
							$this->getConfig()->save();
							if(!$this->getConfig()->exists($args[1])){
								$sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Area removed");
								return true;
							}
							else{
								$sender->sendMessage(TextFormat::RED . "[AreaEffects]Area removal failed");
								return true;
							}
						}
						else{
							$sender->sendMessage(TextFormat::RED . "[AreaEffects]Missing arguments, /ae remove <name>");
						}
						break;
					}
				
				case "list":
					{
						$all = array_keys($this->getConfig()->getAll());
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
	}

	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		foreach(array_keys($this->getConfig()->getAll()) as $areaname){
			if($this->isInArea($player, $areaname)){
				$this->giveEffect($player, $areaname);
			}
		}
	}

	public function isInArea(Player $player, $areaname){
		if(!$this->getConfig()->exists($areaname)){
			$this->getConfig()->reload();
			return false;
		}
		$area = $this->getConfig()->getNested($areaname);
		if(($player->getLevel()->getName() == $area['level']) and ($player->getFloorX() <= max($area['pos1']['x'], $area['pos2']['x'])) and ($player->getFloorX() >= min($area['pos1']['x'], $area['pos2']['x'])) and ($player->getFloorY() <= max($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorY() >= min($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorZ() <= max($area['pos1']['z'], $area['pos2']['z'])) and ($player->getFloorZ() >= min($area['pos1']['z'], $area['pos2']['z']))){
			if(is_null($this->isEffect($area['effect']['id']))){
				$this->getConfig()->remove($areaname);
				$message = TextFormat::YELLOW . "[AreaEffects]Invalid effect found, removed area " . TextFormat::GRAY . $areaname;
				foreach($this->getServer()->getOps() as $opname){
					$op = $this->getServer()->getPlayer($opname);
					if($op instanceof Player && $op->isOnline()) $op->sendMessage($message);
				}
				$this->getLogger()->warning($message);
				return false;
			}
			else{
				return true;
			}
		}
	}

	public function isEffect($effect){
		if(!is_numeric($effect)){
			$id = Effect::getEffectByName($effect);
		}
		else{
			$id = Effect::getEffect($effect);
		}
		return is_null($id)?null:$id->getId();
	}

	public function giveEffect(Player $player, $areaname){
		$area = $this->getConfig()->getNested($areaname);
		$id = $this->isEffect($area['effect']['id']);
		if(!is_null($id)){
			$effect = Effect::getEffect($id);
			$effect->setDuration($area['effect']['duration']);
			$effect->setAmplifier($area['effect']['amplifier']);
			$effect->setVisible($area['effect']['show']);
			$player->removeEffect($id);
			$player->addEffect($effect);
		}
	}
}
