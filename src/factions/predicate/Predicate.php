<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/27/16
 * Time: 7:41 AM
 */

namespace factions\predicate;


abstract class Predicate
{

    const PREDICATE_ONLINE = "online";
    const PREDICATE_OFFLINE = "offline";
    private static $predicates = [

    ];

    public static function init()
    {
        foreach ([self::PREDICATE_ONLINE => OnlinePredicate::class, self::PREDICATE_OFFLINE => OfflinePredicate::class] as $id => $pred) {
            self::$predicates[$id] = new $pred();
        }
    }

    public static function get($predicateId) : Predicate
    {
        return self::$predicates[$predicateId];
    }

    public static function add(Predicate $predicate, string $id) : BOOL
    {
        if (isset(self::$predicates[$id])) return false;
        self::$predicates[$id] = $predicate;
        return true;
    }

    public abstract function apply($to) : BOOL;

}