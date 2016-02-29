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
		$this->makeSaveFiles();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(TextFormat::GREEN . "AreaEffects enabled!");
	}

	private function makeSaveFiles(){
		$this->saveDefaultConfig();
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
							$sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Possition 1 set as x:" . $pos1x . " y:" . $pos1y . " z:" . $pos1z);
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
							$sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Possition 2 set as x:" . $pos2x . " y:" . $pos2y . " z:" . $pos2z);
							return true;
						}
						break;
					}
				
				case "create":
					{
						if($sender instanceof Player){
							if(isset($args[1], $args[2])){
								if(isset($this->pos1, $this->pos2)){
									$this->getConfig()->setNested($args[1], array('pos1' => array('x' => $this->pos1->x,'y' => $this->pos1->y,'z' => $this->pos1->z),'pos2' => array('x' => $this->pos2->x,'y' => $this->pos2->y,'z' => $this->pos2->z),
											'effect' => array('id' => $args[2],'duration' => 200,'amplifier' => 5,'show' => false)));
									$this->saveConfig();
									$sender->sendMessage(TextFormat::GREEN . "[AreaEffects]Area created");
									return true;
								}
							}
							else{
								$sender->sendMessage("Missing arguments, /ae create <name> <id>");
							}
						}
						else{
							$sender->sendMessage(TextFormat::RED . "This command must be used in-game");
						}
						break;
					}
				default:
					{
						return false;
					}
			}
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
		$area = $this->getConfig()->getNested($areaname);
		if(($player->getFloorX() <= max($area['pos1']['x'], $area['pos2']['x'])) and ($player->getFloorY() >= min($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorY() <= max($area['pos1']['y'], $area['pos2']['y'])) and ($player->getFloorZ() >= min($area['pos1']['z'], $area['pos2']['z'])) and ($player->getFloorZ() <= max($area['pos1']['z'], $area['pos2']['z'])) and ($player->getFloorZ() >= min($area['pos1']['z'], $area['pos2']['z']))) return true;
	}

	public function giveEffect(Player $player, $areaname){
		$area = $this->getConfig()->getNested($areaname);
		$id = $area['effect']['id'];
		$effect = Effect::getEffect($id);
		$effect->setDuration($area['effect']['duration']);
		$effect->setAmplifier($area['effect']['amplifier']);
		$effect->setVisible($area['effect']['show']);
		$player->removeEffect($id);
		$player->addEffect($effect);
	}
}
