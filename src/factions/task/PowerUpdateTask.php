<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\task;

use factions\entity\Member;
use factions\manager\Members;
use factions\utils\Gameplay;
use localizer\Localizer;
use pocketmine\scheduler\Task;

class PowerUpdateTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (Members::getAllOnline() as $member) {
            // if($member->isNone()) continue;
            if (!$member instanceof Member) continue;
            if ($member->getPlayer()->isAlive() === false) continue;
            $newPower = $this->calculatePower($member);
            if ($newPower > $member->getPower()) {
                $member->getPlayer()->sendTip(Localizer::trans("power-regen-hud", [
                    "newPower" => $newPower,
                    "diff" => ($newPower - $member->getPower())
                ]));
                $member->setPower($this->calculatePower($member));
            }
        }
    }

    /**
     * Returns new power level
     */
    public function calculatePower(Member $member): int
    {
        if ($member->getPower() === $member->getPowerMax()) return $member->getPower();
        return $member->getPower() + Gameplay::get("power.player.per-update", 1);
    }

}

