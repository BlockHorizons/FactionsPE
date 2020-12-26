<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command\parameter;

use dominate\parameter\Parameter;
use fpe\entity\Faction;
use fpe\manager\Factions;
use fpe\manager\Members;
use localizer\Localizer;
use localizer\Translatable;
use pocketmine\command\CommandSender;

class FactionParameter extends Parameter
{

    const DEFAULT_ERROR_MESSAGE = "type-faction";
    const MEMBER_FACTION_MESSAGE = "type-faction-plus-member";

    /**
     * @param string $name
     * @param MemberParameter|boolean|null $mp
     * @param null $type
     * @param null $index
     */
    public function __construct($name, $mp = null, $type = null, $index = null)
    {
        parent::__construct($name, $type, $index);
        if($mp === true) {
            $this->memberParameter = new MemberParameter($name, MemberParameter::ONLINE_MEMBER, $index);
        }
        $this->memberParameter = $this->memberParameter instanceof MemberParameter ? $this->memberParameter : null;
    }

    /**
     * If this != null, then it will be used to get player by input value. And then return his faction
     * @var MemberParameter|null
     */
    protected $memberParameter;

    public function createErrorMessage(CommandSender $sender, string $value): Translatable
    {
        return Localizer::translatable($this->memberParameter ? self::MEMBER_FACTION_MESSAGE : self::DEFAULT_ERROR_MESSAGE, [
           "value" => $value
        ]);
    }

    /**
     * @param string $input
     * @param CommandSender $sender
     * @return Faction|null
     */
    public function read(string $input, CommandSender $sender = null)
    {
        if (Parameter::isSelfPointer($input) && $sender) {
            $faction = Members::get($sender, true)->getFaction();
        } else {
            $faction = Factions::getByName($input);
            if(!$faction && $this->memberParameter) {
                $member = $this->memberParameter->read($input);
                if($member && $member->hasFaction() || ($member && !$member->hasFaction() && $sender && Members::get($sender)->isOverriding())) {
                    $faction = $member->getFaction();
                }
            }
        }
        return $faction;
    }

    public function isValid($value, CommandSender $sender = null): bool
    {
        return $value instanceof Faction;
    }

}