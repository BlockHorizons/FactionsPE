<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\entity;

use fpe\FactionsPE;
use fpe\manager\Plots;
use pocketmine\level\format\Chunk;
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
        parent::__construct($x);

        if ($x instanceof Position) {
            $this->x = $x->x >> Plots::CHUNK_SIZE;
            $this->z = $x->z >> Plots::CHUNK_SIZE;
            $this->level = $x->level;
        } else {
            $this->x = $x;
            $this->z = $z;
            $this->level = $level;
        }
    }

    public static function fromHash(string $hash): Plot
    {
        list($x, $z, $level) = explode(":", $hash);
        $level = FactionsPE::get()->getServer()->getLevelByName($level);
        $plot = new Plot($x, $z, $level);
        return $plot;
    }

    public function unclaim(IMember $member = null, bool $silent = null)
    {
        Plots::unclaim($this, $member, $silent ?? $member ? false : true);
    }

    public function claim(Faction $faction, IMember $player = null)
    {
        if ($player === null) {
            $player = $faction->getLeader();
        }
        return Plots::claim($faction, $this, $player, false);
    }

    public function isClaimed()
    {
        return $this->getOwnerFaction()->isNone() === false;
    }

    public function getOwnerFaction(): Faction
    {
        return Plots::getFactionAt($this);
    }

    public function getOwnerId(): string
    {
        return $this->getOwnerFaction()->getId();
    }

    public function getPosition(): Position
    {
        return new Position($this->x << Plots::CHUNK_SIZE, 0, $this->z << Plots::CHUNK_SIZE, $this->level);
    }

    public function hash(): string
    {
        return Plots::hash($this);
    }

    public function addX($x): Plot
    {
        $x = ($x instanceof Vector3) ? $x->x : $x;
        $this->x += $x;
        return $this;
    }

    public function addZ($z): Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->z += $z;
        return $this;
    }

    public function subtractX($x): Plot
    {
        $this->subtract($x);
        return $this;
    }

    public function subtractZ($z): Plot
    {
        $z = ($z instanceof Vector3) ? $z->z : $z;
        $this->subtract(0, 0, $z);
        return $this;
    }

    /**
     * Returns chunk that plot sits on or null if invalid plot or level is not loaded
     * @return Chunk|null
     */
    public function getChunk(): ?Chunk
    {
        if ($this->level) {
            return $this->level->getChunk($this->x, $this->z);
        }
        return null;
    }

}
