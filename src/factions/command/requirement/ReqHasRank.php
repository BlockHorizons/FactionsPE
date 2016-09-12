<?php
namespace factions\command\requirement;

use evalcore\requirement\Requirement;
use factions\entity\FPlayer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class ReqHasRank extends Requirement
{

	protected $rank;

	public function __construct($rank) {
		parent::__construct("has-rank");
		$this->rank = $rank;
	}
    
    public function isMet(CommandSender $sender, array $args, $silent = false) : BOOL {
        if($sender instanceof Player) {
        	$fsender = FPlayer::get($sender);
        	if($fsender->getRole() !== $this->rank)
       			if(!$silent) $sender->sendMessage($this->createDeniedMessage($sender));
        	else return true;
        }
        return false;
    }
	
}