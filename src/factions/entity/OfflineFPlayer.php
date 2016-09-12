<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/28/16
 * Time: 11:49 PM
 */

namespace factions\entity;


use factions\data\DataProvider;
use factions\event\faction\FactionDisbandEvent;
use factions\event\player\PlayerMembershipChangeEvent;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\interfaces\RelationParticipator;
use factions\objs\Factions;
use factions\objs\Rel;
use factions\utils\RelationUtil;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\Player;
use pocketmine\Server;

class OfflineFPlayer implements IFPlayer, RelationParticipator {

    /**
     * This is for offline players only to identify them
     * @var String $name
     */
    protected $name;

    /**
     * When the player first time played in faction
     * @var int|null
     */
    protected $firstPlayed = NULL;

    /**
     *
     * @var int|null
     */
    protected $lastPlayed = NULL;

    /**
     * Which "real" player is owner of this?
     * 
     * @var Player|NULL
     */
    protected $player = NULL;
    protected $overriding = false;

    public function getPlayer() {
        return $this->player;
    }

    /**
     * This is a foreign key.
     * Each player belong to a faction.
     * Null means default.
     * @var String $factionId
     */
    protected $factionId = null;

    public function getFactionId() : string {
        if($this->factionId === NULL) return FactionsPE::FACTION_ID_NONE;
        return $this->factionId;
    }

    /** @var int $role */
    protected $role = null;

    /** @var String $title */
    protected $title = null;

    /**
     * Player usually do not have a power boost. It defaults to 0.
     * The powerBoost is a custom increase/decrease to default and maximum power.
     * Note that player powerBoost and faction powerBoost are very similar.
     *
     * @var float $powerBoost
     */
    protected $powerBoost = .0;
    /**
     * Each player has an individual power level.
     * The power level for online players is occasionally updated by a recurring task and the power should stay the same for offline players.
     * For that reason the value is to be considered correct when you pick it. Do not call the power update method.
     * Null means default.
     *
     * @var float $power
     */
    protected $power = .0;


    /**
     * OfflineFPlayer constructor.
     * @param string $name
     */
    public function __construct(string $name) {
        $data = DataProvider::get()->getSavedPlayerData($name);
        $this->name = $name;

        // ** FactionID
        if (isset($data["factionId"])) {
            $this->factionId = $data["factionId"];
            if(!Factions::getById($this->factionId)) {
                $this->factionId = null;
            }
        }
        // ** Role
        if (isset($data["role"])) {
            $this->role = $data["role"];
        } else {
            $this->role = self::getDefaultRole();
        }

        $this->power = isset($data["power"]) ? $data["power"] : self::getDefaultPower();
        $this->powerBoost = isset($data["powerBoost"]) ? $data["powerBoost"] : 0;
        $this->title = isset($data["title"]) ? $data["title"] : NULL;
        $this->lastPlayed = isset($data["lastPlayed"]) ? $data["lastPlayed"] : time();
        $this->firstPlayed = isset($data["firstPlayed"]) ? $data["firstPlayed"] : time();

        FPlayer::attach($this);
    }
    
    public function getName() : STRING {
        if($this->player) return $this->player->getName();
        return $this->name;
    }
    
    public function getNameTag() : STRING {
        if($this->player) return $this->player->getNameTag();
        return $this->name;
    }

    public function isOnline() : BOOL {
        if($this->player) return $this->player->isOnline();
        return false;
    }

    /**
     * To save memory there is no reason for us to save these members because
     * No special/valuable data is stored
     * 
     * @return BOOL
     */
    public function isDefault() : BOOL
    {
        // Last activity millis is data we use for clearing out inactive players. So it does not in itself make the player data worth keeping.
        if ($this->hasFaction()) return false;
        // Role means nothing without a faction.
        // Title means nothing without a faction.
        if ($this->hasPowerBoost()) return false;
        if ($this->getPowerRounded() != (int)round(self::getDefaultPower())) return false;
        // if ($this->isMapAutoUpdating()) return false; // Just having an auto updating map is not in itself reason enough for database storage.
        if ($this->isOverriding()) return false;
        //if ($this->isTerritoryInfoTitles() != Settings::get("Settings.territoryInfoTitlesDefault", "***"))
        return true;
    }

    public function isNone() : BOOL
    {
        return $this->getFactionId() === FactionsPE::FACTION_ID_NONE;
    }

    public function isNormal() : BOOL
    {
        return !$this->isNone();
    }

    // --------------------------------------- //
    // STATIC METHODS
    // --------------------------------------- //

    public static function getDefaultRole() : STRING
    {
        return Settings::get("Settings.defaultPlayerRole", Rel::RECRUIT);
    }

    public static function getDefaultPower() : FLOAT {
        return Settings::get("Settings.defaultPlayerPower", .0);
    }


    // --------------------------------------- //
    // FACTIONS METHODS
    // --------------------------------------- //

    public function getRole() : STRING
    {
        if ($this->role == null) return Settings::get("Settings.defaultPlayerRole", Rel::RECRUIT);
        return $this->role;
    }

    public function resetFactionData()
    {
        // The default neutral faction
        $this->setFactionId(NULL);
        $this->setRole(NULL);
        $this->setTitle(NULL);
    }

    public function leave()
    {
        $myFaction = $this->getFaction();

        $permanent = $myFaction->getFlag(Flag::PERMANENT);

        if (count($this->getFaction()->getPlayers()) > 1)
        {
            if (!$permanent && $this->getRole() === Rel::LEADER)
            {
                $this->sendMessage(Text::parse('faction.leave.as.leader'));
                return;
            }

            if (!Settings::get("Settings.canLeaveWithNegativePower", false) && $this->getPower() < 0)
            {
                $this->sendMessage(Text::parse('faction.leave.with.negative.power'));
                return;
            }
        }

            // Event
        $event = new PlayerMembershipChangeEvent($this, $myFaction, PlayerMembershipChangeEvent::REASON_LEAVE);
		Server::getInstance()->getPluginManager()->callEvent($event);

		if ($event->isCancelled()) return;

		if ($myFaction->isNormal())
        {
            foreach ($myFaction->getPlayersWhereOnline(true) as $player)
			{
                $player->sendMessage(Text::parse("%prefix %var0<i> left %var1<i>.", $this->getDisplayName(), $myFaction->getName()));
            }

			if (Settings::get("Settings.logFactionsLeave", true))
            {
                FactionsPE::get()->getLogger()->info($this->getName()." left the faction: ".$myFaction->getName());
            }
		}

		$this->resetFactionData();

		if ($myFaction->isNormal() && !$permanent && empty($myFaction->getPlayers()))
        {
            $event = new FactionDisbandEvent($this->getFactionId(), $this);
			Server::getInstance()->getPluginManager()->callEvent($event);

			if ( ! $event->isCancelled())
            {
                // Remove this faction
                $this->sendMessage(Text::parse("%var0 <i>was disbanded since you were the last player.", $myFaction->getName()));
                if (Settings::get("Settings.logFactionDisband", true))
                {
                    FactionsPE::get()->getLogger()->info("The faction ".$myFaction->getName()." (".$myFaction->getId().") was disbanded due to the last player (".$this->getName().") leaving.");
                }
                $myFaction->detach();
            }
		}
	}
    
    // ------------------------------ //
    // RANK
    // ------------------------------ //

    public function setRole($role)
    {
        // Detect Nochange
        if ($this->role === $role) return;

        // Apply
        $this->role = $role;

        // Mark as changed
        $this->changed();
    }

    public function isRecruit() : BOOL
    {
        return $this->getRole() === Rel::RECRUIT;
    }

    public function isMember() : BOOL
    {
        return $this->getRole() === Rel::MEMBER;
    }

    public function isOfficer() : BOOL
    {
        return $this->getRole() === Rel::OFFICER;
    }

    public function isLeader() : BOOL
    {
        return $this->getRole() === Rel::LEADER;
    }

    public function setFaction(Faction $faction)
    {
        $this->setFactionId($faction->getId());
    }

    public function setFactionId($factionId)
    {
        // Detect Nochange
        if ($factionId === $this->factionId) return;

        // Get the raw old value
        $oldFactionId = $this->factionId;

        // Apply
        $this->factionId = $factionId;

        if ($oldFactionId == null) $oldFactionId = FactionsPE::FACTION_ID_NONE;

        // Update index
        $oldFaction = Factions::getById($oldFactionId);
        $faction = $this->getFaction();

        //if ($oldFaction != null) $oldFaction->attachPlayer($this);
        //if ($faction != null) $faction->attachPlayer($this);

        $oldFactionIdDesc = "NULL";
        $oldFactionNameDesc = "NULL";
        if ($oldFaction != null) {
            $oldFactionIdDesc = $oldFaction->getId();
            $oldFactionNameDesc = $oldFaction->getName();
        }
        $factionIdDesc = "NULL";
        $factionNameDesc = "NULL";
        if ($faction != null) {
            $factionIdDesc = $faction->getId();
            $factionNameDesc = $faction->getName();
        }

        FactionsPE::get()->getLogger()->info(
           Text::parse("setFactionId moved <h>%var0 <i>aka <h>%var1 <i>from <h>%var2 <i>aka <h>%var3 <i>to <h>%var4 <i>aka <h>%var5<i>.",
                $this->getDisplayName(), $this->getName(), $oldFactionIdDesc, $oldFactionNameDesc, $factionIdDesc, $factionNameDesc));

        // Mark as changed
        $this->changed();
    }

    public function getFaction()
    {
        $ret = Factions::getById($this->getFactionId());
        if ($ret == null) $ret = Faction::get(FactionsPE::FACTION_ID_NONE);
        return $ret;
    }
    
    public function hasPermission(Perm $perm) : BOOL {
        return $this->getFaction()->isPermitted($perm, $this->getRole());
    }

    public function hasFaction() : BOOL
    {
        if(!$this->factionId) return false;
        return $this->factionId !== FactionsPE::FACTION_ID_NONE;
    }

    // ----------------------------------- //
    // PERMISSION
    // ----------------------------------- //


    public function isPermitted(Perm $perm) : BOOL
    {
        return $this->getFaction()->isPermitted($perm, $this->getRole());
    }

    public function isOverriding() : BOOL
    {
        if ($this->overriding === NULL) return false;
        if ($this->overriding === FALSE) return false;

        if ($this->getPlayer() instanceof Player && !$this->getPlayer()->hasPermission(FactionsPE::OVERRIDE)) {
            $this->setOverriding(false);
            return false;
        }

        return true;
    }

    public function setOverriding(bool $overriding)
    {
    if ($overriding === false) $overriding = null;

    // Detect Nochange
    if ($this->overriding === $overriding) return;

    // Apply
    $this->overriding = $overriding;

    // Mark as changed
    $this->changed();
}


    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : STRING
    {
        // TODO: Implement getRelationTo() method.
    }

    // ----------------------------------- //
    // STRING OPERATIONS
    // ----------------------------------- //

    public function getNameAndFactionName() : STRING
    {
        return $this->getNameAndSomething($this->getDisplayName(), $this->getFactionName());
    }


    public function getTitle() : STRING
    {
        if ($this->hasTitle()) return $this->title;
        return Text::parse("player.no.title");
    }
    public function hasTitle() { return $this->title !== NULL; }
    public function setTitle($title) {
        $target = $title;
        
        // Format title
        if ($target != null) {
            $target = trim($target);
            if (strlen($target) == 0) {
                $target = null;
            }
        }
        if($this->title !== $title) $this->changed(); 
    }

    public function getDisplayName() : STRING
    {
        if($this->player) return $this->player->getDisplayName();
        return $this->name;
    }

    public function getNameAndSomething(string $color, string $something) : STRING
    {
        $ret = "";
        $ret .= $color;
        $ret .= Text::getRolePrefix($this->getRole());
        if ($something != null && strlen($something) > 0) {
            $ret .= $something;
            $ret .= " ";
            $ret .= $color;
        }
        $ret .= $this->getName();
        return $ret;
    }

    public function getColorTo(RelationParticipator $observer) : STRING
    {
        // TODO: Implement getColorTo() method.
    }

    public function getNameAndTitleForPlayer(FPlayer $player) : STRING
    {
        return $this->getNameAndTitle($this->getColorTo($player));
    }

    public function describeTo(RelationParticipator $observer, bool $ucfirst = false) : STRING
    {
        return RelationUtil::describeThatToMe($this, $observer, $ucfirst);
    }

    public function getFactionName() : STRING
    {
        $faction = $this->getFaction();
        if ($faction->isNone()) return "";
        return $faction->getName();
    }

    public function getNameAndTitleForFaction(Faction $faction) : STRING
    {
        return $this->getNameAndTitle($this->getColorTo($faction));
    }

    public function getNameAndTitle(string $color) : STRING
    {
        if ($this->hasTitle()) {
            return $this->getNameAndSomething($color, $this->getTitle());
        } else {
            return $this->getNameAndSomething($color, null);
        }
    }

    // ---------------------------------------- //
    // This actually takes quit a lot of space
    //
    // POWER
    // ---------------------------------------- //

    public function getPowerRounded() : INT
    {
        return (int)round($this->getPower());
    }
    
    public function getLimitedPower(float $power) : FLOAT
    {
        $power = max($power, $this->getPowerMin());
        $power = min($power, $this->getPowerMax());

        return $power;
    }

    public function getPowerMin() : FLOAT
    {
        return FactionsPE::get()->getPowerMixin()->getMin($this);
    }

    public function getPowerMax() : FLOAT
    {
        return FactionsPE::get()->getPowerMixin()->getMax($this);
    }

    public function hasPowerBoost() : BOOL {
        return $this->powerBoost !== .0;
    }

    public function setPowerBoost(float $powerBoost)
    {
        $target = $powerBoost;
        if ($target == NULL || $target == 0) $target = NULL;

        // Detect Nochange
        if ($this->powerBoost === $target) return;

        // Apply
        $this->powerBoost = $target;

        // Mark as changed
        $this->changed();
    }

    public function getPower() : FLOAT
    {
        $ret = $this->power;
        if ($ret === NULL) $ret = Settings::get("Settings.defaultPlayerPower", 10);
        $ret = $this->getLimitedPower($ret);
        return $ret;
    }

    public function setPower(float $power)
    {
        // Detect Nochange
        if ($this->power === $power) return;

        // Apply
        echo "Player power set to: ".$power." from {$this->power}\n";
        $this->power = $power;

        // Mark as changed
        $this->changed();
    }


    public function getPowerBoost() : FLOAT
    {
        $ret = $this->powerBoost;
        if ($ret === NULL) $ret = .0;
        return $ret;
    }

    public function getPowerPerHour() : FLOAT
    {
        return FactionsPE::get()->getPowerMixin()->getPerHour($this);
    }

    public function getPowerPerDeath() : FLOAT
    {
        return FactionsPE::get()->getPowerMixin()->getPerDeath($this);
    }

    // MIXIN: FINER

    public function getPowerMaxRounded() : INT
    {
        return (int)round($this->getPowerMax());
    }

    public function getPowerMinRounded() : INT
    {
        return (int)round($this->getPowerMin());
    }

    public function getPowerMaxUniversalRounded() : INT
    {
        return (int)round($this->getPowerMaxUniversal());
    }

    public function getPowerMaxUniversal() : FLOAT
    {
        return FactionsPE::get()->getPowerMixin()->getMaxUniversal($this);
    }

    // ---------------------------------------- //
    // TIME
    // ---------------------------------------- //

    public function getFirstPlayed() : INT {
        return $this->firstPlayed;
    }

    public function getLastPlayed() : INT
    {
        return $this->lastPlayed;
    }

    // ---------------------------------------- //
    // CAPTURING CHANGES ON FPLAYER
    // ---------------------------------------- //


    protected function changed()
    {
        if (FactionsPE::isShuttingDown()) return;

        DataProvider::get()->savePlayerData($this, true);
    }

    public function save() : BOOL
    {
        if(!$this->isDefault()) DataProvider::get()->savePlayerData($this, false);
        return true;
    }

    public function sendMessage(string $message)
    {
        if(!$this->isOnline()) return;
        $this->player->sendMessage($message);
    }
    
}
