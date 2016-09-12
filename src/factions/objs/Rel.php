<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\objs;

use factions\utils\Settings;

final class Rel
{
    // ID's has to be human readable for configurations
    const LEADER = "leader";
    const OFFICER = "officer";
    const MEMBER = "member";
    const RECRUIT = "recruit";
    const ALLY = "ally";
    const TRUCE = "truce";
    const NEUTRAL = "neutral";
    const ENEMY = "enemy";

    public static function isRankValid($rank) : bool
    {
        $rank = self::fromString($rank);
        if ($rank === self::MEMBER or
            $rank === self::OFFICER or
            $rank === self::LEADER
        ) {
            return true;
        }
        return false;
    }

    public static function fromString(string $rel)
    {
        switch (strtolower($rel)) {
            case 'ally':
            case 'allies':
            case 'friend':
            case 'buddy':
            case 'buddies':
            case self::ALLY:
                return self::ALLY;
            case 'truce':
            case 'tru':
            case self::TRUCE:
                return self::TRUCE;
            case 'neutral':
            case 'neu':
            case self::NEUTRAL:
                return self::NEUTRAL;
            case 'enemy':
            case 'ene':
            case self::ENEMY:
                return self::ENEMY;
            case 'leader':
            case 'lea':
            case self::LEADER:
                return self::LEADER;
            case 'officer':
            case 'off':
                return self::OFFICER;
            case 'member':
            case 'mem':
                return self::MEMBER;
            case 'recruit':
            case 'rec':
                return self::RECRUIT;
        }
        return NULL;
    }

    public static function isFriend($relation) : BOOL {
        if($relation === Rel::ALLY) return true;
        return false;
    }

    public static function getColor($rel)
    {
        $rel = self::fromString($rel);

        switch ($rel) {
            case self::ALLY:
                return Settings::get('color.ally', "");
            case self::NEUTRAL:
                return Settings::get('color.neutral', "");
            case self::TRUCE:
                return Settings::get('color.truce', "");
            case self::ENEMY:
                return Settings::get('color.enemy', "");
            case self::RECRUIT:
                return Settings::get('color.recruit', "");
            case self::MEMBER:
                return Settings::get('color.member', "");
            case self::OFFICER:
                return Settings::get('color.officer', "");
            case self::LEADER:
                return Settings::get('color.leader', "");
            default:
                return "";
        }
    }

    public static function getAll()
    {
        return [self::LEADER, self::OFFICER, self::MEMBER, self::RECRUIT, self::NEUTRAL, self::ALLY, self::TRUCE, self::ENEMY];
    }

}