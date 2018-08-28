<?php
namespace factions\engine;

use pocketmine\level\Level;
use pocketmine\level\format\Chunk;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;

use localizer\Localizer;

use factions\FactionsPE;
use factions\entity\Member;
use factions\utils\Gameplay;
use factions\event\member\MemberTraceEvent;

class SeeChunkEngine extends Engine {

    /** 
     * Holds player and chunk objects
     * @var mixed[] 
     */
    protected $chunks = [];

    protected $step = 4;
    protected $density = 1;
    protected $yOffset = 0.8;

    const PLAYER               = 0;
    const CHUNK                = 1;
    const BRUSH 			   = 2;
    const LEVEL 			   = 3;
    const DEFAULT_INTERVAL     = 5;

    public function setup()
    {
        # Do nothing
    }

    /**
     * @priority MONITOR
     * @param MemberTraceEvent
     */
    public function onTrace(MemberTraceEvent $event) {
    	$member = $event->getMember();
    	if($member->isSeeingChunk()) {
    		$this->removeChunk($member);
    		$this->setChunk($member, $event->getTo()->getChunk(), $event->getTo()->getLevel());
    	}
    }

    /**
     * Creates particles which will be used to show edges of chunk
     * @param Chunk $chunk
     * @param float $step Must divide 16 with no remain
     * @param float $density Particles per step
     * @param string $particle class name
     * @param float $defaultY Usually player's y if there is no blocks around
     * @param float $yOffset = 0.8 How high from land the particles will spawn
     *
     * @return Particle[]
     */
    public function createBrush(Chunk $chunk, float $step = 2, float $density = 1, string $particle, float $defaultY, float $yOffset = 0.8): array {
    	if(16 % $step) throw new \InvalidArgumentException("invalid step(=$step) for chunk {$chunk->getX()}:{$chunk->getY} remainder: " . 16 % step);
    	$particles = [];
    	$minX = $chunk->getX() << 4;
    	$minZ = $chunk->getZ() << 4;
  		$endX = $minX + 16;
  		$endZ = $minZ + 16;
    	for($x = $minX; $x <= $endX; $x += $step) {
    		# Side 1
    		$particles[] = $this->makeParticleFor($x, $defaultY, $minZ, $particle);
    	}
    	for($z = $minZ + $step; $z <= $endZ; $z += $step) {
    		# Side 2
    		$particles[] = $this->makeParticleFor($endX, $defaultY, $z, $particle);
    	}
    	for($x = $endX - $step; $x >= $minX; $x -= $step) {
    		# Side 3
    		$particles[] = $this->makeParticleFor($x, $defaultY, $endZ, $particle);
    	}
    	for($z = $endZ - $step; $z >= $minZ + $step; $z -= $step) {
    		# Side 2
    		$particles[] = $this->makeParticleFor($minX, $defaultY, $z, $particle);
    	}
    	return $particles;
    }

    private function getY(Chunk $chunk, float $x, float $z): float {
    	$y = $chunk->getHighestBlockAt($x % 16, $z % 16);
    	if($y < 0) $y = $defaultY;
    	return $y;
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $particle class name
     *
     * @return Particle
     */
    public function makeParticleFor(float $x, float $y, float $z, string $particle): Particle {
    	return new $particle(new Vector3($x, $y, $z), self::DEFAULT_INTERVAL);
    }

    /**
     * Set the chunk player sees. Only one chunk per player
     * @param Member $player
     * @param Chunk $chunk
     */
    public function setChunk(Member $player, Chunk $chunk, Level $level): void {
        $this->chunks[strtolower($player->getName())] = [
            self::PLAYER => $player,
            self::CHUNK => $chunk,
            self::BRUSH => $this->createBrush($chunk, $this->step, $this->density, RedstoneParticle::CLASS, $player->getPlayer()->getFloorX(), $this->yOffset),
            self::LEVEL => $level
        ];
        if(!$this->isLooping()) $this->startLoop(Gameplay::get('seechunk-task-interval', self::DEFAULT_INTERVAL));
    }

    public function removeChunk($player) {
        $player = $player instanceof Member ? strtolower($player->getName()) : strtolower($player);
        unset($this->chunks[$player]);
        if(empty($this->chunks) && $this->isLooping()) $this->stopLoop(); // Don't waste CPU
    }

    public function isSeeingChunk(Member $player, bool $test = true): ?bool {
        $c = $this->chunks[strtolower($player->getName())][self::CHUNK] ?? null;
        if($sc = $player->isSeeingChunk()) {
            return $c !== null;
        // Drawing this chunk without request (seechunk disabled, without telling the Engine)
        } elseif ($test && !$sc && $c !== null) {
            throw new \LogicException("player '{$player->getName()}' is seeing chunk {$c->getX()}:{$c->getZ()} with invalid request");
        }
        return false;
    }

    public function drawChunk(string $name): void {
        if(!$this->chunks[$name][self::LEVEL]->isClosed()) {
        	$ps = null;
        	foreach($this->chunks[$name][self::BRUSH] as $particle) {
        		for($y = max(0, $this->chunks[$name][self::PLAYER]->getPlayer()->getY() - 16), $endY = $y + 32; $y <= $endY; $y += 8) {
        			$particle->y = $y;
        			$this->chunks[$name][self::LEVEL]->addParticle($particle, [$this->chunks[$name][self::PLAYER]->getPlayer()]);
        		}
        		$particle->y = $y;
        	}
        }
    }

    public function onCancel() {
    	foreach($this->chunks as $data) {
    		$data[self::PLAYER]->setSeeChunk(false);
    		$data[self::PLAYER]->sendMessage(Localizer::translatable('see-chunk-disabled-engine-stop'));
    	}
    	$this->chunks = [];
    }

    /**
     * Draws chunks
     *
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach($this->chunks as $name => $data) {
            $this->drawChunk($name);
        }
    }
	
}