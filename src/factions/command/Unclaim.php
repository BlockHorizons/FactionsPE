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

use dominate\Command;
use dominate\parameter\Parameter;
use dominate\requirement\SimpleRequirement;

use factions\command\requirement\FactionRequirement;
use factions\manager\Permissions;

class Unclaim extends Command {

    public function setup() {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));

        $plugin = $this->getPlugin();
        $this->addChild((new ClaimOne($plugin, "one", "Unclaim one plot", Permissions::UNCLAIM_ONE, ["1"]))->setClaim(false));
        // $this->addChild((new ClaimAuto($plugin))->setClaim(true));
        // $this->addChild((new ClaimFill($plugin))->setClaim(true));
        // $this->addChild((new ClaimSquare($plugin))->setClaim(true));
        // $this->addChild((new ClaimCircle($plugin))->setclaim(true));

        $this->addParameter((new Parameter("one"))->setDefaultValue("one"));
    }

}