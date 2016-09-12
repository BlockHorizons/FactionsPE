<?php
namespace factions\task;

use factions\FactionsPE;
use factions\utils\HUD;
use pocketmine\scheduler\PluginTask;

class HUDTask extends PluginTask
{

    /** @var String $hud */
    protected $hud;

    public function __construct(FactionsPE $owner)
    {
        parent::__construct($owner);
    }

    public function onRun($currentTick)
    {

        foreach (HUD::get()->getViewers() as $player) {

            HUD::get()->send($player);

        }

    }

}