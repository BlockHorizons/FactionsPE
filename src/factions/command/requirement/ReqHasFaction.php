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
use factions\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class ReqHasFaction extends Requirement
{
    
    public function __construct() {
        parent::__construct("has-faction");
    }

    public function isMet(CommandSender $sender, array $args, $silent = false) : BOOL
    {
        $ret = false;
        if($sender instanceof Player) {
            $fplayer = FPlayer::get($sender);
            $ret = $fplayer->hasFaction();
            if(!$ret and !$silent) $sender->sendMessage($this->createDeniedMessage($sender));
        }
        return $ret;
    }

    public function createDeniedMessage(CommandSender $sender) : STRING
    {
        return Text::parse('requirement.has_faction.deny');
    }

}