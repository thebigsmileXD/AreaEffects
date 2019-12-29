<?php

namespace xenialdan\AreaEffects;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
    public $areas;
    private static $instance;

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->getConfig()->reload();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getLogger()->info(TextFormat::GREEN . "AreaEffects enabled!");
    }

    public function isEffect($effect)
    {
        if (!is_numeric($effect)) {
            $id = Effect::getEffectByName($effect);
        } else {
            $id = Effect::getEffect($effect);
        }
        return is_null($id) ? null : $id->getId();
    }

    public function giveEffect(Player $player, $areaname)
    {
        $area = $this->getConfig()->getNested($areaname);
        $id = $this->isEffect($area['effect']['id']);
        if (!is_null($id)) {
            $effectInstance = new EffectInstance(Effect::getEffect($id), $area['effect']['duration'], $area['effect']['amplifier'], $area['effect']['show']);
            $player->removeEffect($id);
            $player->addEffect($effectInstance);
        }
    }
}
