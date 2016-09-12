<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/26/16
 * Time: 6:07 PM
 */

namespace factions\interfaces;


interface RelationParticipator
{
    public function describeTo(RelationParticipator $observer, bool $ucFirst = false) : STRING;

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : STRING;

    public function getColorTo(RelationParticipator $observer) : STRING;
}