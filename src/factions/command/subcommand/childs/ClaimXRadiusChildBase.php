<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/15/16
 * Time: 11:55 AM
 */

namespace factions\command\subcommand\childs;

use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeInteger;
use factions\command\parameter\type\TypeFaction;
use factions\entity\FPlayer;
use factions\FactionsPE;
use factions\utils\Settings;
use factions\utils\Text;

abstract class ClaimXRadiusChildBase extends ClaimXChildBase
{
    protected $radius = 0;

    public function __construct(FactionsPE $plugin, $name, $description, $permission, $claim, array $aliases = [], array $requirements = [])
    {
        parent::__construct($plugin, $name, $description, $permission, $claim, $aliases, $requirements);

        $this->addParameter(new Parameter("radius", new TypeInteger(), false, true));
        $this->addParameter(new Parameter("faction", new TypeFaction(), false, false, "me"));
    }

    public function getRadius() {
        $fsender = FPlayer::get($this->sender);
        $radius = $this->args[0];
        if($radius < 1) {
            $this->sender->sendMessage(Text::parse("<rose>If you specify radius, it must be at least 1."));
            return false;
        }
        // Radius Claim Max
        if ($radius > Settings::get("setRadiusMax", 50) && ! $fsender->isOverriding())
        {
            $this->sender->sendMessage(Text::parse("<b>The maximum radius allowed is <h>%s<b>.", Settings::get("setRadiusMax", 50)));
            return false;
        }

        $this->radius = $radius;
        return $radius;
    }
    
    public function getRadiusZero() : INT {
        $radius = $this->getRadius();
        if($radius > 0) return $radius - 1;
        return 0;
    }
}