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

use factions\utils\Gameplay;
use factions\utils\Text;
use pocketmine\factions\TextFormat;

final class Relation
{

    private static $relLevels = array(
        Rel::RECRUIT => 1000,
        Rel::MEMBER => 2000,
        Rel::OFFICER => 3000,
        Rel::LEADER => 4000,
        // Relations
        Rel::ALLY => 5000,
        Rel::NEUTRAL => 6000,
        Rel::TRUCE => 7000,
        Rel::ENEMY => 8000
    );

    private function __construct(){}

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
            $rank === self::LEADER or
            $rank === self::RECRUIT
        ) {
            return true;
        }
        return false;
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


    public static function isFriend(string $relation) : bool {
        return $relation === self::ALLY;
    }

    public static function isEnemy(string $relation) : bool {
        return $relation === self::ENEMY;
    }


    public static function getColor($rel) : string
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

    public static function getAll() : array
    {
        return [self::LEADER, self::OFFICER, self::MEMBER, self::RECRUIT, self::NEUTRAL, self::ALLY, self::TRUCE, self::ENEMY];
    }

    public static function getRelationOfThatToMe(RelationParticipator $me, RelationParticipator $that, bool $ignorePeaceful = false) : string {
        $ret = self::NEUTRAL;
        $myFaction = self::getFaction($me);
        if ($myFaction === null) return self::NEUTRAL; // ERROR
        $thatFaction = self::getFaction($that);
        if ($thatFaction === null) return self::NEUTRAL; // ERROR
        if ($myFaction === $thatFaction) {
            $ret = self::MEMBER;
            // Do officer and leader check
            //P.p.log("getRelationOfThatToMe the factions are the same for "+that.getClass().getSimpleName()+" and observer "+me.getClass().getSimpleName());
            if ($that instanceof IMember) {
                $ret = $that->getRole();
                //P.p.log("getRelationOfThatToMe it was a player and role is "+ret);
            }
        } else if (!$ignorePeaceful && ($thatFaction->getFlag(Flag::PEACEFUL) || $myFaction->getFlag(Flag::PEACEFUL))) {
            $ret = self::TRUCE;
        } else {
            // The faction with the lowest wish "wins"
            if (self::isLowerThan($thatFaction->getRelationWish($myFaction), $myFaction->getRelationWish($thatFaction))) {
                $ret = $thatFaction->getRelationWish($myFaction);
            } else {
                $ret = $myFaction->getRelationWish($thatFaction);
            }
        }
        return $ret;
    }

    public static function getFaction(RelationParticipator $object) {
        if ($object instanceof Faction) {
            return $object;
        } elseif ($object instanceof IMember) {
            return $object->getFaction();
        }
        # Error
        return NULL;
    }

    public static function getColorOfThatToMe(RelationParticipator $me, RelationParticipator $that)  : string {
        # TODO
        return TextFormat::DARK_GREEN;
    }

    public static function isAtLeast($relA, $relB) : bool {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA >= $lB;
    }

    public static function isLowerThan($relA, $relB) : bool {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA < $lB;
    }

    public static function isHigherThan($relA, $relB) : bool {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA < $lB;
    }

}