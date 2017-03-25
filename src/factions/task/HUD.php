<?php

namespace factions\task;

use factions\entity\Member;
use factions\FactionsPE;
use factions\manager\Members;
use factions\utils\Gameplay;
use factions\utils\Text;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class HUD extends PluginTask
{

    const DEFAULT_HUD = "》 Power: &6{POWER}&f/&6{MAXPOWER} &8|&f Money: &6{MU}{MONEY} &8|&f Faction: &6{FACTION} &8|&f Faction power: &6{F-POWER}&f/&6{MAXF-POWER}&f 《";

    /** @var string */
    protected $hud = self::DEFAULT_HUD;

    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->hud = Gameplay::get("hud.popup", $this->hud);
    }

    public function onRun($currentTick)
    {
        foreach (Members::getAllOnline() as $member) {
            if (!$member->hasHUD()) continue;

            $member->getPlayer()->sendPopup(Text::parse(self::parseTags($member, $this->hud)));
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

}
