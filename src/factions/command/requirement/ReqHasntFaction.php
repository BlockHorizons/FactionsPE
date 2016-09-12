<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/19/16
 * Time: 10:41 PM
 */

namespace factions\command\requirement;


use evalcore\requirement\Requirement;
use factions\entity\FPlayer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class ReqHasntFaction extends Requirement
{

    public function __construct() {
        parent::__construct("has-not-faction");
    }

    public function isMet(CommandSender $sender, array $args, $silent = false) : BOOL
    {
        if(FPlayer::get($sender)->hasFaction()) {
            if(!$silent) $sender->sendMessage($this->createDeniedMessage($sender));
            return false;
        }
        return true;
    }

}