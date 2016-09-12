<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/28/16
 * Time: 4:57 PM
 */

namespace factions\predicate;


use factions\interfaces\IFPlayer;
use pocketmine\Player;

class OnlinePredicate extends Predicate
{

    public function apply($player) : BOOL
    {
        if ($player instanceof Player or $player instanceof IFPlayer) return $player->isOnline();
        return false;
    }

}