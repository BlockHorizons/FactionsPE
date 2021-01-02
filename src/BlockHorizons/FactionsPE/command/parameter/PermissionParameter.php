<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command\parameter;

use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use pocketmine\command\CommandSender;

class PermissionParameter extends Parameter
{

    const ALL = 20;
    const ANY = 21;
    const ONE = 22;

    public function setup()
    {
        $this->ERROR_MESSAGES = [
            self::ALL => "type-perm-all",
            self::ANY => "type-perm-any",
            self::ONE => "type-perm-one"
        ];
    }

    public function read(string $input, CommandSender $sender = null)
    {
        if (strtolower($input) === "all") {
            return Permissions::getAll();
        }
        $perm = Permissions::getById(strtolower($input));
        if ($perm && !$perm->isVisible() && $sender) {
            if (!Members::get($sender)->isOverriding()) {
                $perm = null;
            }
        }
        return $perm;
    }

    public function isValid($value, CommandSender $sender = null): bool
    {
        switch ($this->type) {
            case self::ALL:
                return is_array($value);
            case self::ANY:
                return $value !== null;
            case self::ONE:
                return $value instanceof Permission;
            default:
                return false;
        }
    }

}