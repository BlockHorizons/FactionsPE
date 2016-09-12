<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/28/16
 * Time: 5:36 PM
 */

namespace factions\mixin;


use factions\interfaces\IFPlayer;
use factions\utils\Settings;

class PowerMixin
{

    private static $instance;
    public function __construct() {self::$instance = $this;}
    public static function get(){return self::$instance;}

    public function getMaxUniversal(IFPlayer $player) : INT
    {
        return .0;
    }

    public function getMax(IFPlayer $player) : INT
    {
        return 10.0;
    }

    public function getMin(IFPlayer $player) : INT
    {
        return .0;
    }

    public function getPerHour(IFPlayer $player) : INT
    {
        return Settings::get('power.perHour', .1);
    }

    public function getPerDeath(IFPlayer $player) : INT
    {
        return Settings::get('power.perDeath', .1);
    }

}