<?php
namespace factions\command\parameter\type;


use evalcore\command\parameter\type\Type;
use factions\entity\Perm;
use pocketmine\command\CommandSender;

class TypePerm extends Type
{

    public function __construct($name="perm")
    {
        parent::__construct($name);
    }

    public function read(string $arg, CommandSender $sender = null, $silent = false)
    {
        if($arg === "all") return Perm::getAll();
        if(($perm = Perm::getPermById($arg)) instanceof Perm) {
            if(!$perm->isVisible()) return false;
            return $perm;
        }
        return null;
    }

    public function isValid(CommandSender $sender, string $arg) : BOOL
    {
        return $this->read($arg, $sender, true) != null;
    }
}