<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/27/16
 * Time: 3:24 PM
 */

namespace factions\utils;


use factions\entity\Flag;
use factions\interfaces\IFPlayer;
use factions\interfaces\RelationParticipator;
use factions\objs\Rel;
use pocketmine\utils\TextFormat;
use factions\entity\Faction;

class RelationUtil
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

    public static function describeThatToMe(RelationParticipator $this, RelationParticipator $observer, bool $ucfirst = false)  : STRING
    {
        # TODO
        return "";
    }

    public static function getRelationOfThatToMe(RelationParticipator $me, RelationParticipator $that, bool $ignorePeaceful = false) : STRING
    {
        $ret = Rel::NEUTRAL;

        $myFaction = self::getFaction($me);
        if ($myFaction === null) return Rel::NEUTRAL; // ERROR

        $thatFaction = self::getFaction($that);
        if ($thatFaction === null) return Rel::NEUTRAL; // ERROR

        if ($myFaction === $thatFaction) {
            $ret = Rel::MEMBER;
            // Do officer and leader check
            //P.p.log("getRelationOfThatToMe the factions are the same for "+that.getClass().getSimpleName()+" and observer "+me.getClass().getSimpleName());
            if ($that instanceof IFPlayer) {
                $ret = $that->getRole();
                //P.p.log("getRelationOfThatToMe it was a player and role is "+ret);
            }
        } else if (!$ignorePeaceful && ($thatFaction->getFlag(Flag::PEACEFUL) || $myFaction->getFlag(Flag::PEACEFUL))) {
            $ret = Rel::TRUCE;
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

    public static function getFaction(RelationParticipator $object)
    {
        if ($object instanceof Faction) {
            return $object;
        } elseif ($object instanceof IFPlayer) {
            return $object->getFaction();
        }
        # Error
        return NULL;
    }

    public static function getColorOfThatToMe(RelationParticipator $me, RelationParticipator $that)  : STRING
    {
        # TODO
        return TextFormat::DARK_GREEN;
    }

    public static function isAtLeast($relA, $relB) : BOOL
    {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA >= $lB;
    }

    public static function isLowerThan($relA, $relB) : BOOL
    {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA < $lB;
    }

    public static function isHigherThan($relA, $relB) : BOOL
    {
        $lA = isset(self::$relLevels[$relA]) ? self::$relLevels[$relA] : 0;
        $lB = isset(self::$relLevels[$relB]) ? self::$relLevels[$relB] : 0;
        return $lA < $lB;
    }
}