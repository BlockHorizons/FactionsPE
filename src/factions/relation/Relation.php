<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\relation;

use factions\entity\Faction;
use factions\entity\IMember;
use factions\flag\Flag;
use factions\utils\Gameplay;
use factions\utils\Text;
use pocketmine\utils\TextFormat;

final class Relation
{

    const NONE = "none";
    const LEADER = "leader";

    // ID's has to be human readable for configurations
    const OFFICER = "officer";
    const MEMBER = "member";
    const RECRUIT = "recruit";
    const ALLY = "ally";
    const TRUCE = "truce";
    const NEUTRAL = "neutral";
    const ENEMY = "enemy";
    private static $relLevels = array(
        self::NONE => 0,
        self::RECRUIT => 1000,
        self::MEMBER => 2000,
        self::OFFICER => 3000,
        self::LEADER => 4000,
        // Relations
        self::ALLY => 5000,
        self::NEUTRAL => 6000,
        self::TRUCE => 7000,
        self::ENEMY => 8000
    );

    private function __construct()
    {
    }

    public static function isValid(string $rel): bool
    {
        return in_array(self::fromString($rel), self::getAll(), true);
    }

    public static function fromString(string $rel)
    {
        switch (strtolower(trim($rel))) {
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
        return null;
    }

    public static function getAll(): array
    {
        return [self::RECRUIT, self::MEMBER, self::OFFICER, self::LEADER, self::ALLY, self::TRUCE, self::NEUTRAL, self::ENEMY];
    }

    public static function isFriend(string $relation): bool
    {
        return $relation === self::ALLY || self::isRankValid($relation);
    }

    public static function isRankValid($rank): bool
    {
        #return self::isLowerThan(self::fromString($rank), self::ALLY);
        if (!$rank) return false;
        return in_array(self::fromString($rank), [self::LEADER, self::OFFICER, self::MEMBER, self::RECRUIT], true);
    }

    public static function isEnemy(string $relation): bool
    {
        return $relation === self::ENEMY;
    }

    public static function getRelationOfThatToMe(RelationParticipator $me, RelationParticipator $that, bool $ignorePeaceful = false): string
    {
        $ret = self::NEUTRAL;
        $myFaction = self::getFaction($me);
        if ($myFaction === null) return self::NEUTRAL; // ERROR
        $thatFaction = self::getFaction($that);
        if ($thatFaction === null) return self::NEUTRAL; // ERROR
        if ($myFaction === $thatFaction) {
            $ret = self::MEMBER;
            // Do officer and leader check
            if ($that instanceof IMember) {
                $ret = $that->getRole();
            }
        } else if (!$ignorePeaceful && ($thatFaction->getFlag(Flag::PEACEFUL) || $myFaction->getFlag(Flag::PEACEFUL))) {
            $ret = self::TRUCE;
        } else {
            // The faction with the lowest wish "wins"
            if (self::isLowerThan(($tw = $thatFaction->getRelationWish($myFaction)), ($mw = $myFaction->getRelationWish($thatFaction)))) {
                return $tw;
            } else {
                return $mw;
            }
        }
        return $ret;
    }

    public static function getFaction(RelationParticipator $object)
    {
        if ($object instanceof Faction) {
            return $object;
        } elseif ($object instanceof IMember) {
            if ($object->hasFaction()) {
                return $object->getFaction();
            } else {
                return null;
            }
        }
        # Error
        return null;
    }

    public static function isLowerThan($relA, $relB): bool
    {
        $lA = self::$relLevels[$relA] ?? 0;
        $lB = self::$relLevels[$relB] ?? 0;
        return $lA < $lB;
    }

    public static function getColorOfThatToMe(RelationParticipator $me, RelationParticipator $that): string
    {
        return self::getColor($me->getRelationTo($that));
    }

    public static function getColor($rel): string
    {
        $rel = self::fromString($rel);
        switch ($rel) {
            case self::ALLY:
                return Text::parseColorVars(Gameplay::get('color.ally', TextFormat::WHITE));
            case self::NEUTRAL:
                return Text::parseColorVars(Gameplay::get('color.neutral', TextFormat::WHITE));
            case self::TRUCE:
                return Text::parseColorVars(Gameplay::get('color.truce', TextFormat::WHITE));
            case self::ENEMY:
                return Text::parseColorVars(Gameplay::get('color.enemy', TextFormat::WHITE));
            case self::RECRUIT:
                return Text::parseColorVars(Gameplay::get('color.recruit', TextFormat::WHITE));
            case self::MEMBER:
                return Text::parseColorVars(Gameplay::get('color.member', TextFormat::WHITE));
            case self::OFFICER:
                return Text::parseColorVars(Gameplay::get('color.officer', TextFormat::WHITE));
            case self::LEADER:
                return Text::parseColorVars(Gameplay::get('color.leader', TextFormat::WHITE));
            default:
                return TextFormat::WHITE;
        }
    }

    /**
     * Returns 1 if A is Higher than B
     * Returns 0 If A is Equal to B
     * Returns -1 If A is Lower than B
     * Returns 2 on error
     */
    public static function compare($relA, $relB): int
    {
        if (self::isHigherThan($relA, $relB)) return 1;
        if (self::isAtLeast($relA, $relB)) return 0;
        if (self::isLowerThan($relA, $relB)) return -1;
        return 2; # ERROR
    }

    public static function isHigherThan($relA, $relB): bool
    {
        $lA = self::$relLevels[$relA] ?? 0;
        $lB = self::$relLevels[$relB] ?? 0;
        return $lA > $lB;
    }

    public static function isAtLeast($relA, $relB): bool
    {
        $lA = self::$relLevels[$relA] ?? 0;
        $lB = self::$relLevels[$relB] ?? 0;
        return $lA >= $lB;
    }

    public static function getNext($rel)
    {
        switch ($rel) {
            case self::RECRUIT:
                return self::MEMBER;
            case self::MEMBER:
                return self::OFFICER;
            case self::OFFICER:
                return self::LEADER;
            default:
                return null;
        }
    }

    public static function getPrevious($rel)
    {
        switch ($rel) {
            case self::LEADER:
                return self::OFFICER;
            case self::OFFICER:
                return self::MEMBER;
            case self::MEMBER:
                return self::RECRUIT;
            default:
                return null;
        }
    }

}
