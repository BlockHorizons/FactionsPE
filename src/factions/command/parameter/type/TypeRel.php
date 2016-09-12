<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/12/16
 * Time: 10:49 PM
 */

namespace factions\command\parameter\type;


use evalcore\command\parameter\type\Type;
use factions\objs\Rel;
use pocketmine\command\CommandSender;

class TypeRel extends Type
{

    protected $allowAll = true;

    public function __construct($name="relation")
    {
        parent::__construct($name);
    }

    public function read(string $arg, CommandSender $sender = null, $silent = false)
    {
        if(strtolower($arg) === "all" and $this->allowAll) return Rel::getAll();
        return Rel::fromString($arg);
    }

    public function isValid(CommandSender $sender, string $arg) : BOOL
    {
        return $this->read($arg, $sender) !== null;
    }

    public function allowAll(bool $value) : TypeRel {
        $this->allowAll = $value;
        return $this;
    }

}