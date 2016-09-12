<?php
namespace factions\entity;

use factions\FactionsPE;
use factions\interfaces\RelationParticipator;
use factions\objs\Rel;
use factions\interfaces\IFPlayer;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class FConsole implements IFPlayer, RelationParticipator {

	private $console;

	public function __construct() {
		$this->console = new ConsoleCommandSender();
	}
	
	public function getFactionId() : string {
		return FactionsPE::FACTION_ID_NONE;
	}

	// --------------------------- //
	//	FACTIONS
	// --------------------------- //

	/**
	 * @return Faction
	 */
	public function getFaction() : Faction {
		return Factions::getById(FactionsPE::FACTION_ID_NONE);
	}

	public function setFaction(Faction $faction) {
		$this->setFactionId($faction->getId());
	}

	public function setFactionId($id) {
		throw new \LogicException("invalid call on console object");
	}

	// --------------------------- //
	// STRING IDENTIFIERS
	// --------------------------- //

	public function getName() : string {
		return "Console";
	}

	public function getTitle() : string {
		return "Console";
	}

	public function getDisplayName() : string {
		return "Console";
	}

	// --------------------------- //
	//	POWER & ROLE
	// --------------------------- //
	
	public function getRole() : string {
		return Rel::LEADER;
	}

	public function getPower() : float {
		return (float) PHP_INT_MAX;
	}

	public function getPowerBoost() : float {
		return (float) PHP_INT_MAX;
	}

	public function setRole($role) {
		throw new \LogicException("invalid call on console object");
	}

	public function setPower(float $power) {
		throw new \LogicException("invalid call on console object");
	}

	public function setPowerBoost(float $boost) {
		throw new \LogicException("invalid call on console object");
	}

	public function getPowerRounded() : INT {
		return PHP_INT_MAX;
	}

	public function getLimitedPower(float $power) : float {
		return (int) $power;
	}

	public function getPowerMin() : float {
		return (float) PHP_INT_MIN;
	}

	public function getPowerMax() : float {
		return (float) PHP_INT_MAX;
	}

	public function getPowerMaxUniversal() : float {
		return $this->getPowerMax();
	}

	// --------------------------- //
	//	MISC
	// --------------------------- //

	public function getFirstPlayed() : int {
		return 0;
	}

	public function getLastPlayed() : int {
		return time();
	}

	public function isDefault() : bool {
		return true;
	}

	public function isNone() : bool {
		return true;
	}

	public function isNormal() : bool {
		return false;
	}

	public function isOnline() : bool {
		return true;
	}

	public function isOverriding() : bool {
		return true;
	}

	public function hasFaction() : bool {
		return false;
	}

	public function hasPowerBoost() : bool {
		return $this->getPowerBoost() !== 0;
	}

	public function hasPermission(Perm $perm) : bool {
		return true;
	}

	public function sendMessage(string $message) {
		$this->console->sendMessage("[FConsole]: ".$message);
	}

	public function save(){}

	public function describeTo(RelationParticipator $observer, bool $ucFirst = false) : STRING
	{
		if($ucFirst) return ucfirst($this->getName());
		return $this->getName();
	}

	public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : STRING
	{
		return Rel::NEUTRAL;
	}

	public function getColorTo(RelationParticipator $observer) : STRING
	{
		return TextFormat::WHITE;
	}
}