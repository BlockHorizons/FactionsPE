<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\command\requirement\FactionRequirement;
use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\dominate\requirement\SimpleRequirement;
use fpe\manager\Permissions;

class Claim extends Command
{

    public function setup()
    {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));

        $plugin = $this->getPlugin();
        $this->addChild((new ClaimOne($plugin, "one", "Claim one plot", Permissions::CLAIM_ONE, ["1"]))->setClaim(true));
        $this->addChild((new ClaimAuto($plugin, "auto", "Set auto claiming", Permissions::CLAIM_AUTO))->setClaim(true));
        // $this->addChild((new ClaimFill($plugin))->setClaim(true));
        $this->addChild((new ClaimSquare($plugin, "square", "Claim a square", Permissions::CLAIM_SQUARE))->setClaim(true));
        // $this->addChild((new ClaimCircle($plugin))->setclaim(true));

        $this->addParameter((new Parameter("square|circle|auto|one"))->setDefaultValue("one"));
    }

}
