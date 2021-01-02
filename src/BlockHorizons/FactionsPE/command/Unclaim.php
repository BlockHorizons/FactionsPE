<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\requirement\FactionRequirement;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\manager\Permissions;

class Unclaim extends Command
{

    public function setup()
    {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));

        $plugin = $this->getPlugin();
        $this->addChild((new ClaimOne($plugin, "one", "Unclaim one plot", Permissions::UNCLAIM_ONE, ["1"]))->setClaim(false));
        $this->addChild((new ClaimAuto($plugin, "auto", "Set auto claiming", Permissions::CLAIM_AUTO))->setClaim(false));
        // $this->addChild((new ClaimFill($plugin))->setClaim(true));
        $this->addChild((new ClaimSquare($plugin, "square", "Unclaim a square of plots", Permissions::CLAIM_SQUARE))->setClaim(false));
        // $this->addChild((new ClaimCircle($plugin))->setclaim(true));
        //$this->addChild((new ClaimAll($plugin, "all", "Unclaim all faction plots", Permissions::CLAIM_ALL))->setClaim(false));

        $this->addParameter((new Parameter("square|circle|auto|one|all"))->setDefaultValue("one"));
    }

}