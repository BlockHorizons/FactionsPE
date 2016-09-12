<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/16/16
 * Time: 3:28 PM
 */

namespace factions\entity;


use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\objs\Plots;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class Plot extends Position
{

    /**
     * Plot constructor.
     * @param Position|int $x
     * @param int $z
     * @param Level|null $level
     */
    public function __construct($x, $z = 0, Level $level = null)
    {
        parent::__construct($x, 0, $z, $level);
        if($x instanceof Position) {
            $this->x = $x->x >> 4;
            $this->z = $x->z >> 4;
            $this->level = $x->level;
        } else {
            $this->x = $x;
            $this->z = $z;
            $this->level = $level;
        }
    }

    public static function fromHash(string $hash) : Plot
    {
        list($x, $z, $level) = explode(":", $hash);
        $level = FactionsPE::get()->getServer()->getLevelByName($level);
        $plot = new Plot($x, $z, $level);
        return $plot;
    }

    public function unclaim()
    {
        Plots::get()->unclaim($this, false);
    }

    public function claim(Faction $faction, IFPlayer $player = null)
    {
        if ($player === null) {
            $player = $faction->getLeader();
        }
        return Plots::get()->claim($faction, $player, $this, false);
    }

    public function isClaimed()
    {
        return $this->getOwnerFaction()->isNone() === false;
    }

    public function getOwnerFaction() : Faction
    {
        return Plots::get()->getFactionAt($this);
    }

    public function getPosition() : Position
    {
        return new Position($this->x << 4, 0, $this->z << 4, $this->level);
    }

    public function hash() : STRING
    {
        return $this->x . ":" . $this->z . ":" . $this->level->getName();
    }

    public function addX($x) : Plot
    {
        return $this->add($x);
    }

    public function addZ($z) : Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->add(0, 0, $z);
        return $this;
    }

    public function subtractX($x) : Plot
    {
        $this->subtract($x);
        return $this;
    }

    public function subtractZ($z) : Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->subtract(0, 0, $z);
        return $this;
    }


}