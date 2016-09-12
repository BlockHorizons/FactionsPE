<?php
namespace factions\command\parameter\type;

use evalcore\command\parameter\type\Type;
use factions\entity\FPlayer;
use factions\objs\Factions;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class TypeFaction extends Type
{

    public function __construct($name="faction")
    {
        parent::__construct($name);
    }

    public function read(string $arg, CommandSender $sender = null, $silent = false)
    {
        if($arg === "me") {
            if(!$sender instanceof Player){
                if(!$silent) $sender->sendMessage($this->createErrorMessage($sender, $arg));
                return false;
            }
            $fplayer = FPlayer::get($sender);
            if($fplayer->hasFaction()) return $fplayer->getFaction();
            if(!$silent) $sender->sendMessage($this->createErrorMessage($sender, $arg));
            return false;
        }
        if(!($f = Factions::getByName($arg))){
            if(!$silent) $sender->sendMessage($this->createErrorMessage($sender, $arg));
            return false;
        }
        return $f;
    }
    
    public function isValid(CommandSender $sender, string $arg) : BOOL
    {
        return $this->read($arg, $sender, true) !== false;
    }

}