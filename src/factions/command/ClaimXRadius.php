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

namespace factions\command;

use dominate\parameter\Parameter;
use factions\manager\Members;
use factions\utils\Gameplay;
use localizer\Localizer;

abstract class ClaimXRadius extends ClaimX
{

    protected $radius = 0;

    public function setup()
    {
        $this->setFactionArgIndex(1);
        $this->addParameter(new Parameter("radius", Parameter::TYPE_INTEGER));
        parent::setup();
    }

    /**
     * @param
     * @return bool|int
     */
    public function getRadius()
    {
        $msender = Members::get($this->sender);
        $radius = $this->getArgument(0);
        if ($radius < 1) {
            $this->sender->sendMessage(Localizer::translatable("invalid-radius"));
            return false;
        }
        // Radius Claim Max
        if ($radius > Gameplay::get("set-radius-max", 5) && !$msender->isOverriding()) {
            $msender->sendMessage(Localizer::translatable("radius-exceeds-allowed", [Gameplay::get("set-radius-max", 5)]));
            return false;
        }
        $this->radius = $radius;
        return $radius;
    }

    /**
     * Remember to call ClaimXRadius::getRadius() at first
     */
    public function getRadiusZero(): int
    {
        $radius = $this->radius;
        if ($radius > 0) return $radius - 1;
        return 0;
    }
}