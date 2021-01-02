<?php

namespace BlockHorizons\FactionsPE\task;

use BlockHorizons\FactionsPE\entity\Member;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Gameplay;
use BlockHorizons\FactionsPE\utils\Text;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class HUD extends Task
{

    const DEFAULT_POPUP = "》 Power: &6{POWER}&f/&6{MAXPOWER} &8|&f Money: &6{MU}{MONEY} &8|&f Faction: &6{FACTION} &8|&f Faction power: &6{F-POWER}&f/&6{MAXF-POWER}&f 《";
    const DEFAULT_TIP = null;

    /** @var string */
    protected $popup = self::DEFAULT_POPUP;
    protected $tip = self::DEFAULT_TIP;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->popup = Gameplay::get("hud.popup", $this->popup);
        $this->tip = Gameplay::get('hud.tip', $this->tip);
    }

    public function getPlugin(): FactionsPE
    {
        return $this->plugin;
    }

    public function onRun(int $currentTick)
    {
        foreach (Members::getAllOnline() as $member) {
            if (!$member->hasHUD()) continue;

            $member->getPlayer()->sendPopup(Text::parse(self::parseTags($member, $this->popup)));
            if ($this->tip) $member->getPlayer()->sendTip(Text::parse(self::parseTags($member, $this->tip)));
        }
    }

    public static function parseTags(Member $member, string $hud): string
    {
        $hf = $member->hasFaction();
        return str_replace(["{NAME}", "{MONEY}", "{FACTION}", "{POWER}", "{MAXPOWER}", "{F-POWER}", "{MAXF-POWER}", "{MU}"], [
            $member->getDisplayName(),
            self::getMoney($member),
            $hf ? $member->getFaction()->getName() : "<red>none",
            $member->getPower(),
            $member->getPowerMax(),
            $hf ? $member->getFaction()->getPower(true) : "<red>NaN",
            $hf ? $member->getFaction()->getPowerMax() : "<red>NaN",
            FactionsPE::get()->getEconomy() ? FactionsPE::get()->getEconomy()->getMoneyUnit() : ""
        ], $hud);
    }

    public static function getMoney(Member $member): int
    {
        return ($e = FactionsPE::get()->getEconomy()) ? $e->balance($member->getPlayer()) : 0;
    }

    public function setTip(string $tip)
    {
        $this->tip = $tip;
    }

    public function setPopup(string $popup)
    {
        $this->popup = $popup;
    }

    // public function setInterval(int $interval) {
    // # TODO        
    // }

}
