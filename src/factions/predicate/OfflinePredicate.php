<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/3/16
 * Time: 3:22 PM
 */

namespace factions\predicate;


use factions\interfaces\IFPlayer;

class OfflinePredicate extends Predicate
{

    public function apply($to) : BOOL
    {
        /** @var IFPlayer $to */
        return !$to->isOnline();
    }
}