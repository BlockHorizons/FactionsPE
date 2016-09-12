<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/29/16
 * Time: 12:15 AM
 */

namespace factions\interfaces;


use factions\entity\Faction;
use factions\entity\Perm;

interface IFPlayer
{

    public function getFactionId() : STRING;

    /**
     * @return Faction|null
     */
    public function getFaction();
    public function setFaction(Faction $faction);
    public function setFactionId($factionId);

    public function getName() : STRING;
    public function getTitle() : STRING;
    public function getDisplayName() : STRING;

    public function getRole() : STRING;
    public function getPower() : FLOAT;
    public function getPowerBoost() : FLOAT;
    public function setRole($role);
    public function setPower(float $power);
    public function setPowerBoost(float $power);
    
    // MIXIN
    public function getPowerRounded() : INT;
    public function getLimitedPower(float $power) : FLOAT;
    public function getPowerMin() : FLOAT;
    public function getPowerMax() : FLOAT;
    public function getPowerMaxUniversal() : FLOAT;

    public function getFirstPlayed() : INT;
    public function getLastPlayed() : INT;

    public function isDefault() : BOOL;
    public function isNone() : BOOL;
    public function isNormal() : BOOL;
    public function isOnline() : BOOL;
    public function isOverriding() : BOOL;
    public function hasFaction() : BOOL;
    public function hasPowerBoost() : BOOL;

    /**
     * @param Perm|string $perm
     * @return BOOL
     */
    public function hasPermission(Perm $perm) : BOOL;

    public function sendMessage(string $message);

    public function save();

} 