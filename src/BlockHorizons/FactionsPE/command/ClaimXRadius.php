<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Gameplay;
use BlockHorizons\FactionsPE\localizer\Localizer;

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