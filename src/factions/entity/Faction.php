<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\entity;


//use factions\EconomyParticipator;
use evalcore\EvalCore;
use factions\data\DataProvider;
use factions\FactionsPE;
use factions\interfaces\IFPlayer;
use factions\interfaces\Named;
use factions\interfaces\RelationParticipator;
use factions\objs\Factions;
use factions\objs\Plots;
use factions\objs\Rel;
use factions\predicate\Predicate;
use factions\utils\RelationUtil;
use factions\utils\Settings;
use factions\utils\Text;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Faction implements RelationParticipator, Named
{
    
    // ----------------------------------------- //
    // STATIC
    // ----------------------------------------- //
    public static function get(string $id)
    {
        return Factions::getById($id);
    }

    public function detach()
    {
        Factions::detach($this);
    }
    public function attach(){
        Factions::attach($this);
    }
    
    // -------------------------------------------- //
    // FIELDS: RAW
    // -------------------------------------------- //
    // In this section of the source code we place the field declarations only.
    // Each field has it's own section further down since just the getter and setter logic takes up quite some place.
    
    /**
     * This is where we save faction members
     * It restricts FPlayer instances un-synchronized attaching to faction
     *
     * @var FPlayer[]
     */
    protected $players = [];

    /** @var String $name */
    private $name = null;

    /** 
     * Factions can optionally set a description for themselves.
     * This description can for example be seen in territorial alerts.
     * Null means the faction has no description.
     * @var string
     */
    private $description = null;
    
    /** 
     * Factions can optionally set a message of the day.
     * This message will be shown when logging on to the server.
     * Null means the faction has no motd
     * @var string $motd 
     */
    private $motd = null;
    
    /**
     * We store the creation date for the faction. It can be displayed on info pages etc. 
     * @var int $createdAtMillis 
     */
    private $createdAtMillis = 0;

    /**
     * Factions can optionally set a home location.
     * If they do their members can teleport there using /f home
     * Null means the faction has no home.
     * @var Position|null
     */
    private $home = null;

    /** 
     * Factions usually do not have a powerboost. It defaults to 0.
     * The powerBoost is a custom increase/decrease to default and maximum power.
     * Null means the faction has powerBoost (0).
     */
    private $powerBoost = null;
    
    private $invitedPlayers = [];
    
    /**
     * FactionID => Rel ID 
     * @var array $relationWishes 
     */
    private $relationWishes = [];

    /**
     * The keys in this map are factionIds.
     * @var array $flags Flag => bool 
     */
    private $flags = [];
    
    /**
     * Saves last time when this faction were saved. (UNIX Timestamp) 
     * @var int $lastSave 
     */
    private $lastSave;
    
    /**
     * The perm overrides are modifications to the default values.
     * @var array
     */ 
    protected $perms = [];

    /**
     * The actual faction id looks something like "54947df8-0e9e-4471-a2f9-9af509fb5889" and that is not too easy to remember for humans.
     * Thus we make use of a name. Since the id is used in all foreign key situations changing the name is fine.
     * Null should never happen. The name must not be null.
     * @param string
     */
    private $id;

    /**
     * Faction constructor. If creating new faction - pass leader object inside $data
     * 
     * @param string $name
     * @param string $id
     * @param array $data
     */
    public function __construct(string $name, string $id, array $data = [])
    {
        $this->name = $name;
        $this->id = $id;
        if (isset($data["description"])) $this->description = $data["description"];
        if (isset($data["motd"])) $this->motd = $data["motd"];
        if (isset($data["createdAtMillis"])) $this->createdAtMillis = $data["createdAtMillis"];
        else $this->createdAtMillis = time();
        if (isset($data["powerBoost"])) $this->powerBoost = $data["powerBoost"];
        if (isset($data["invitedPlayers"])) $this->invitedPlayers = $data["invitedPlayers"];
        if (isset($data["relationWishes"])) $this->relationWishes = $data["relationWishes"];
        if (isset($data["flags"])) $this->flags = $data["flags"];
        if (isset($data["perms"])) $this->perms = $data["perms"];
        if (isset($data["players"])) {
            foreach($data["players"] as $player) {
                $player = $player instanceof IFPlayer ? $player : FPlayer::get($player);
                $player->setFaction($this);

                $this->players[] = $player;
            }
        }

       if(isset($data["home"])) {
           $p = explode(":", $data["home"]);
           if(($level = EvalCore::get()->getServer()->getLevelByName($p[3]))) {
               $pos = new Position($p[0], $p[1], $p[2], $level);
               $this->home = $pos;
               $this->verifyHomeIsValid();
           } else {
               EvalCore::warning(Text::parse("<red>%var0 home was un-set because it is no longer in valid territory (%var1)", $this->getName(), $data["home"]));
           }
       }

        if(!Factions::isRegistered($this)) Factions::attach($this);
        Plots::get()->registerFaction($this);

        $this->checkFPlayerIndex();
    }

    public function setFlagIds(array $flagIds)
    {
        // Clean input
        $target = [];
        foreach ($flagIds as $key => $value) {
            $target[$key] = $value;
        }

        // Detect Nochange
        if ($this->flags === $target) return;

        // Apply
        $this->flags = $target;

        // Mark as changed
        $this->save();
    }

    public function setPermIds(array $perms)
    {
        // Clean input
        $target = [];
        foreach ($perms as $key => $value) {
            if ($key == null) continue;
            $key = strtolower($key); // Lowercased Keys Version 2.6.0 --> 2.7.0
            if ($value == null) continue;

            $target[$key] = $value;
        }

        // Detect Nochange
        if ($this->perms === $target) return;

        // Apply
        $this->perms = $target;

        // Mark as changed
        $this->save();
    }

    public function isNormal() : BOOL
    {
        return !$this->isNone();
    }

    // FINER

    public function isNone() : BOOL
    {
        return $this->getId() === FactionsPE::FACTION_ID_NONE;
    }

    public function getId()
    {
        return $this->id;
    }

    // -------------------------------------------- //
    // FIELD: description
    // -------------------------------------------- //

    // RAW

    public function getNameFor(RelationParticipator $observer) : STRING
    {
        if ($observer == null) return $this->getName();
        return $this->getPrefixedName($this->getColorTo($observer));
    }

    public function getName() : STRING
    {
        $ret = $this->name;

        if (Settings::get("forceNameToUpperCase", false)) {
            $ret = strtoupper($ret);
        }

        return $ret;
    }

    public function setName(string $name)
    {
        // Clean input
        $target = $name;

        // Detect Nochange
        if ($this->name === $target) return;

        // Apply
        $this->name = $target;

        // Mark as changed
        $$this->changed();
    }

    // -------------------------------------------- //
    // FIELD: motd
    // -------------------------------------------- //

    // RAW

    public function getPrefixedName(string $prefix) : STRING
    {
        return $prefix . $this->getName();
    }

    public function getColorTo(RelationParticipator $observer) : STRING
    {
        return RelationUtil::getColorOfThatToMe($this, $observer);
    }

    public function getDescription() : STRING
    {
        if ($this->hasDescription()) return Text::parse($this->description);
        return Text::parse('faction.no.description');
    }

    // FINER

    public function setDescription(string $description)
    {
        // Clean input
        $target = $description;
        if ($target != null) {
            $target = trim($target);
        }

        // Detect Nochange
        if ($this->description === $target) return;

        // Apply
        $this->description = $target;

        // Mark as changed
        $this->save();
    }

    // -------------------------------------------- //
    // FIELD: createdAtMillis
    // -------------------------------------------- //

    public function hasDescription() : BOOL
    {
        return $this->description != null;
    }

    public function getMotdMessages() : ARRAY
    {
        // Create
        $ret = [];

        // Fill
        $title = $this->getName() . " - Message of the Day";
        //title = Txt.titleize(title); TODO
        $ret[] = $title;

        $motd = Text::parse("@i" . $this->getMotd());
        $ret[] = $motd;
        $ret[] = "";

        // Return
        return $ret;
    }

    // -------------------------------------------- //
    // FIELD: home
    // -------------------------------------------- //

    public function getMotd() : STRING
    {
        if ($this->hasMotd()) return Text::parseColorVars($this->motd);
        return Text::parse("faction.no.motd");
    }

    public function setMotd(string $description)
    {
        // Clean input
        $target = $description;
        if ($target != null) {
            $target = trim($target);
            if (strlen($target) == 0) {
                $target = null;
            }
        }

        // Detect Nochange
        if ($this->motd === $target) return;

        // Apply
        $this->motd = $target;

        // Mark as changed
        $this->save();
    }

    public function hasMotd() : BOOL
    {
        return $this->motd != null;
    }

    /**
     * @return int
     */
    public function getCreatedAtMillis()
    {
        return $this->createdAtMillis;
    }

    public function setCreatedAtMillis($createdAtMillis)
    {
        // Clean input
        $target = $createdAtMillis;

        // Detect Nochange
        if ($this->createdAtMillis === $target) return;

        // Apply
        $this->createdAtMillis = $target;

        // Mark as changed
        $this->save();
    }

    // -------------------------------------------- //
    // FIELD: powerBoost
    // -------------------------------------------- //

    // RAW

    public function hasHome() : BOOL
    {
        return $this->getHome() != null;
    }

    /**
     * @return null|Position
     */
    public function getHome()
    {
        $this->verifyHomeIsValid();
        return $this->home;
    }

    // -------------------------------------------- //
    // FIELD: open
    // -------------------------------------------- //

    // Nowadays this is a flag!

    public function setHome(Position $home)
    {
        // Clean input
        $target = $home;

        // Detect Nochange
        if ($this->home === $home) return;

        // Apply
        $this->home = $target;

        // Mark as changed
        $this->save();
    }

    public function verifyHomeIsValid()
    {
        if($this->home == null) return;
        if ($this->isValidHome($this->home)) return;
        $this->home = null;
        $this->save();
        $this->sendMessage(Text::parse("Your faction home has been un-set since it is no longer in your territory."));
    }

    public function isValidHome($pos) : BOOL
    {
        if ($pos === null) return false;
        if (!$pos instanceof Position) return false;
        if (!Settings::get("homesMustBeInClaimedTerritories", true)) return true;
        if (Plots::get()->getFactionAt($pos) === $this) return true;
        return false;
    }

    // -------------------------------------------- //
    // FIELD: invitedPlayerIds
    // -------------------------------------------- //

    // RAW

    public function sendMessage($message)
    {
        foreach ($this->getOnlinePlayers() as $player) {
            $player->sendMessage($message);
        }
    }

    public function getOnlinePlayers() : ARRAY
    {
        // Create Ret
        $ret = [];

        // Fill Ret
        foreach (FactionsPE::get()->getServer()->getOnlinePlayers() as $player) {
            $fplayer = FPlayer::get($player);
            if ($fplayer->getFaction() !== $this) continue;

            $ret[] = $player;
        }

        // Return Ret
        return $ret;
    }

    // FINER

    public function isDefaultOpen() : BOOL
    {
        return Flag::getFlagOpen()->isStandard();
    }

    public function isOpen() : BOOL
    {
        return $this->getFlag(Flag::OPEN);
    }

    public function getFlag(string $flagId) : BOOL
    {
        if ($flagId === NULL) throw new \Exception("flagId"); // NullPointerException

        $ret = isset($this->flags[$flagId]) ? $this->flags[$flagId] : NULL;
        if ($ret !== NULL) return $ret;

        $flag = Flag::getFlagById($flagId);
        if ($flag === NULL) throw new \Exception("flag");

        return $flag->isStandard();
    }

    // -------------------------------------------- //
    // FIELD: relationWish
    // -------------------------------------------- //

    // RAW

    public function setOpen(Boolean $open)
    {
        $flag = Flag::OPEN;
        $this->setFlag($flag, $open);
    }

    public function setFlag(string $flagId, bool $value) : BOOL
    {
        if ($flagId == null) throw new \Exception("flagId");

        $ret = $this->flags[$flagId] = $value;
        if ($ret === NULL || $ret !== $value) $this->save();
        return $ret;
    }

    // FINER

    public function isInvited($param) : BOOL
    {
        $param = $param instanceof FPlayer ? $param->getName() : $param;
        return in_array(strtolower($param), $this->getInvitedPlayers(), true);
    }

    public function getInvitedPlayers() : ARRAY
    {
        return $this->invitedPlayers;
    }

    // -------------------------------------------- //
    // FIELD: flagOverrides
    // -------------------------------------------- //

    // RAW

    public function setInvitedPlayers(array $invitedPlayerIds)
    {
        // Clean input
        $target = $invitedPlayerIds;
        if ($invitedPlayerIds != null) {
            foreach ($invitedPlayerIds as $invitedPlayerId) {
                $target[] = strtolower($invitedPlayerId);
            }
        }

        // Detect Nochange
        if ($this->invitedPlayers === $target) return;

        // Apply
        $this->invitedPlayers = $target;

        // Mark as changed
        $this->save();
    }

    public function setInvited($player, bool $invited) : BOOL
    {
        $playerId = $player instanceof IFPlayer ? $player->getName() : $player;
        $invitedPlayers = $this->getInvitedPlayers();
        if ($invited) {
            $invitedPlayers[] = strtolower($playerId);
        } else {
            unset($invitedPlayers[array_search(strtolower($playerId), $invitedPlayers, true)]);
        }
        $this->setInvitedPlayers($invitedPlayers);
        return true;

    }

    public function getInvitedFPlayers() : ARRAY
    {
        $players = [];

        foreach ($this->getInvitedPlayers() as $id) {
            $player = FPlayer::get($id);
            if ($player instanceof FPlayer) $players[] = $player;
        }
        return $players;
    }

    // FINER

    public function getRelationWish($faction) : string
    {
        $factionId = $faction instanceof Faction ? $faction->getId() : $faction;
        $ret = isset($this->relationWishes[$factionId]) ? $this->relationWishes[$factionId] : NULL;
        if ($ret === NULL) $ret = Rel::NEUTRAL;
        return $ret;
    }

    public function setRelationWish($faction, int $rel)
    {
        $factionId = $faction instanceof Faction ? $faction->getId() : $faction;
        $relationWishes = $this->getRelationWishes();
        if ($rel == null || $rel == Rel::NEUTRAL) {
            unset($relationWishes[array_search($factionId, $relationWishes, true)]);
        } else {
            $relationWishes[$factionId] = $rel;
        }
        $this->setRelationWishes($relationWishes);
    }

    // -------------------------------------------- //
    // FIELD: permOverrides
    // -------------------------------------------- //

    // RAW

    public function getRelationWishes() : ARRAY
    {
        return $this->relationWishes;
    }

    public function setRelationWishes(array $relationWishes)
    {
        // Clean input
        $target = $relationWishes;

        // Detect Nochange
        if ($this->relationWishes === $target) return;

        // Apply
        $this->relationWishes = $target;

        // Mark as changed
        $this->save();
    }

    /**
     * @return array
     */
    public function getFlags() : ARRAY
    {
        // We start with default values ...
        $ret = [];
        foreach (Flag::getAll() as $flag) {
            $ret[$flag->getId()] = $flag->isStandard();
        }
        // Then overwrite with our.
        foreach($this->flags as $flag => $value) {
            $ret[$flag] = $value;
        }

        return $ret;
    }

    // FINER

    public function setFlags(array $flags)
    {
        $flagIds = [];
        foreach ($flags as $flag) {
            $flagIds[$flag->getId()] = $flag->isStandard();
        }
        $this->setFlagIds($flagIds);
    }

    // ---

    public function setPermittedRelations(Perm $perm, array $rels)
    {
        $perms = $this->getPerms();
        $perms[$perm->getId()] = $rels;
        $this->setPerms($perms);
    }

    // ---
    // TODO: Fix these below. They are reworking the whole map.

    public function getPerms() : ARRAY
    {
        // We start with default values ...
        $ret = [];
        foreach (Perm::getAll() as $perm) {
            $ret[$perm->getId()] = $perm->getStandard();
        }

        // ... and if anything is explicitly set we use that info...
        foreach ($this->perms as $permId => $relations) {
            $ret[$permId] = $relations;
        }

        return $ret;
    }

    public function setPerms(array $perms)
    {
        $permIds = [];
        foreach ($perms as $key => $value) {
            $permIds[$key] = $value;
        }
        $this->setPermIds($permIds);
    }

    // -------------------------------------------- //
    // OVERRIDE: RelationParticipator
    // -------------------------------------------- //

    public function setRelationPermitted(Perm $perm, string $rel, bool $permitted)
    {
        $perms = $this->getPerms();

        $relations = $this->getPermitted($perm);

        if ($permitted and !$this->isPermitted($perm, $rel)) {
            $relations[] = $rel;
        } else {
            unset($relations[array_search($rel, $relations, true)]);
        }
        $perms[$perm->getId()] = $relations;

        $this->setPerms($perms);

        $this->save();
    }

    public function getPermitted(Perm $perm) : ARRAY
    {
        $rels = NULL;
        if(isset($this->getPerms()[$perm->getId()])) $rels = $this->getPerms()[$perm->getId()];
        if($rels !== null) return $rels;
        return $perm->getStandard();
    }

    public function isPermitted(Perm $perm, string $rel) : BOOL
    {
        $rels = $this->getPermitted($perm);

        return in_array($rel, $rels, true);
    }

    // -------------------------------------------- //
    // POWER
    // -------------------------------------------- //
    // TODO: Implement a has enough feature.

    public function describeTo(RelationParticipator $observer, bool $ucfirst = false) : STRING
    {
        return RelationUtil::describeThatToMe($this, $observer, $ucfirst);
    }

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false) : STRING
    {
        return RelationUtil::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
    }

    public function getPowerMaxRounded() : INT
    {
        return (int)round($this->getPowerMax());
    }

    public function getPowerMax() : float
    {
        if ($this->getFlag(Flag::INFINITY_POWER)) return 999999;

        $ret = 0;
        foreach ($this->getPlayers() as $player) {
            $ret += $player->getPowerMax();
        }

        $factionPowerMax = Settings::get("factionPowerMax", 100);
        if ($factionPowerMax > 0 && $ret > $factionPowerMax) {
            $ret = $factionPowerMax;
        }

        $ret += $this->getPowerBoost();

        return $ret;
    }

    /**
     * @return IFPlayer[]
     */
    public function getPlayers() : ARRAY
    {
        $players = [];
        foreach(FPlayer::getAll() as $player) {
            if($player->getFactionId() === $this->getId()) $players[] = $player;
        }
        $this->players = $players;
        return $players;
    }

    public function getPowerBoost() : float
    {
        $ret = $this->powerBoost;
        if ($ret == null) $ret = 0;
        return $ret;
    }

    public function setPowerBoost(float $powerBoost)
    {
        // Clean input
        $target = $powerBoost;

        if ($target == null || $target == 0) $target = null;

        // Detect Nochange
        if ($this->powerBoost === $target) return;

        // Apply
        $this->powerBoost = $target;

        // Mark as changed
        $this->save();
    }

    public function isSpecial()
    {
        return $this->id === FactionsPE::FACTION_ID_NONE ||
        $this->id === FactionsPE::FACTION_ID_SAFEZONE ||
        $this->id === FactionsPE::FACTION_ID_WARZONE;
    }

    public function hasLandInflation() : BOOL
    {
        return $this->getLandCount() > $this->getPowerRounded();
    }

    // TODO: Even though this check method removeds the invalid entries it's not a true solution.
    // TODO: Find the bug causing non-attached MPlayers to be present in the index.

    public function getLandCount() : INT
    {
        return Plots::get()->getCount($this);
    }

    public function getPowerRounded() : INT
    {
        return (int)round($this->getPower());
    }

    public function getPower() : float
    {
        if ($this->getFlag(Flag::INFINITY_POWER)) return 999999;

        $ret = 0;
        foreach ($this->getPlayers() as $fplayer) {
            $ret += $fplayer->getPower();
        }

        $factionPowerMax = Settings::get("factionPowerMax", 100);
        if ($factionPowerMax > 0 && $ret > $factionPowerMax) {
            $ret = $factionPowerMax;
        }

        $ret += $this->getPowerBoost();

        return $ret;
    }

    public function reindexFPlayers()
    {
        $this->players = [];

        $factionId = $this->getId();
        if ($factionId == null) return;

        foreach (FPlayer::getAll() as $player) {
            if ($player->getFactionId() !== $factionId) continue;
            $this->players[] = $player;
        }
    }

    public function promoteNewLeader(FPlayer $leader = NULL)
    {
        if ($this->isNone()) return;
        if ($this->getFlag(Flag::PERMANENT) && Settings::get("permanentFactionsDisableLeaderPromotion", true)) return;
        if ($leader and !$leader->hasFaction() or $leader->getFaction() !== $this) return;

        $oldLeader = $this->getLeader();

        // get list of officers, or list of normal members if there are no officers
        $replacements = $leader instanceof FPlayer ? [$leader] : $this->getPlayersWhereRole(Rel::OFFICER);
        if (empty($replacements)) {
            $replacements = $this->getPlayersWhereRole(Rel::MEMBER);
        }

        if (empty($replacements)) {
            // faction leader is the only member; one-man faction
            if ($this->getFlag(Flag::PERMANENT)) {
                if ($oldLeader != null) {
                    // TODO: Where is the logic in this? Why MEMBER? Why not LEADER again? And why not OFFICER or RECRUIT?
                    $oldLeader->setRole(Rel::MEMBER);
                }
                return;
            }

            // no members left and faction isn't permanent, so disband it
            if (Settings::get("logFactionDisband", true)) {
                FactionsPE::get()->getLogger()->info("The faction " . $this->getName() . " (" . $this->getId() . ") has been disbanded since it has no members left.");
            }

            foreach (FPlayer::getAllOnline() as $player) {
                $player->sendMessage(Text::parse("<i>The faction %var0<i> was disbanded.", $this->getName()));
            }

            $this->detach();
        } else {
            // promote new faction leader
            if ($oldLeader != null) {
                $oldLeader->setRole(Rel::MEMBER);
            }
            /** @var FPlayer[] $replacements */
            $replacements[0]->setRole(Rel::LEADER);
            $this->sendMessage(Text::parse("<i>Faction leader <h>%var0<i> has been removed. %var1<i> has been promoted as the new faction leader.", $oldLeader == null ? "" : $oldLeader->getName(), $replacements[0]->getName()));
            FactionsPE::get()->getLogger()->info("Faction " . $this->getName() . " (" . $this->getId() . ") leader was removed. Replacement leader: " . $replacements[0]->getName());
        }
    }

    /**
     * Returns this Faction's leader, if returned null and this faction isn't special
     * Then this object is considered invalid and must be destroyed or new Leader has to be attached.
     * As invalid faction object in runtime may cause an unexpected behaviour
     * 
     * @return FPlayer|NULL
     */
    public function getLeader()
    {
        $ret = $this->getPlayersWhereRole(Rel::LEADER);
        if (empty($ret)) return null;
        return $ret[0];
    }

    // used when current leader is about to be removed from the faction; promotes new leader, or disbands faction if no other members left

    private function getPlayersWhereRole(string $rel)
    {
        $ret = [];
        foreach ($this->getPlayers() as $player) {
            if ($player->getRole() === $rel) $ret[] = $player;
        }
        return $ret;
    }

    // -------------------------------------------- //
    // FACTION ONLINE STATE
    // -------------------------------------------- //

    /**
     * Is any of member online?
     * @return bool
     */
    public function isAnyFPlayersOnline() : BOOL
    {
        return !$this->isAllFPlayersOffline();
    }

    /**
     * Is all players offline?
     * @return bool
     */
    public function isAllFPlayersOffline() : bool
    {
        return count($this->getPlayersWhereOnline(true)) == 0;
    }

    /**
     * Get online/offline players
     * @param bool $online
     * @return IFPlayer[]
     */
    public function getPlayersWhereOnline(bool $online = true) : ARRAY
    {
        return $this->getPlayersWhere($online ? Predicate::get(Predicate::PREDICATE_ONLINE) : Predicate::get(Predicate::PREDICATE_OFFLINE));
    }

    /**
     * Returns list of IFPlayers which can apply to prediction
     * @param Predicate $predicate
     * @return array
     */
    public function getPlayersWhere(Predicate $predicate) : ARRAY
    {
        $ret = [];
        foreach ($this->getPlayers() as $player) {
            if ($predicate->apply($player)) $ret[] = $player;
        }
        return $ret;
    }

    /**
     * Returns true if explosion can occur on this Faction's land
     * @return bool
     * @throws \Exception
     */
    public function isExplosionsAllowed() : bool
    {
        $explosions = $this->getFlag(Flag::EXPLOSIONS);
        $offlineexplosions = $this->getFlag(Flag::OFFLINE_EXPLOSIONS);

        if ($explosions && $offlineexplosions) return true;
        if (!$explosions && !$offlineexplosions) return false;

        $online = $this->isFactionConsideredOnline();

        return ($online && $explosions) || (!$online && $offlineexplosions);
    }

    /**
     * Faction is considered online when at least one of member is online
     * @return bool
     */
    public function isFactionConsideredOnline() : BOOL
    {
        return !$this->isFactionConsideredOffline();
    }

    /**
     * Faction is considered offline when none of the members are online
     * @return bool
     */
    public function isFactionConsideredOffline() : bool
    {
        return $this->isAllFPlayersOffline();
    }
    
    // ------------------------------------------- //
    // PLOTS
    // ------------------------------------------- //

    /**
     * Get how mony plots has this Faction claimed in specific world
     * @param Level $world
     * @return int
     */
    public function getLandCountInWorld(Level $world)
    {
        return count($this->getPlotsInWorld($world));
    }

    /**
     * Get Plots in specific world
     * @param Level $world
     * @return Plot[]
     */
    public function getPlotsInWorld(Level $world)
    {
        return Plots::get()->getFactionPlotsInWorld($this, $world);
    }

    /**
     * Kicks players that has attached and isn't member of this faction
     */
    private function checkFPlayerIndex()
    {
        foreach ($this->players as $player) {
            if (!$this->verifyMember($player)) {
                $msg = Text::parse("<rose>WARN: <i>Faction <h>%var0 <i>aka <h>%var1 <i>had unattached fplayer in index:", $this->getName(), $this->getId());
                FactionsPE::get()->getLogger()->warning($msg);

                unset($this->players[array_search($player, $this->players, true)]);
            }
        }
    }

    /**
     * Get last online time
     * @return int (UNIX Timestamp)
     */
    public function getLastOnline() : int {
        $last = 0;
        foreach($this->getPlayers() as $player) {
            if($last < $player->getLastPlayed()) 
                $last = $player->getLastPlayed();
        }
        return $last;
    }

    /**
     * Returns list of Factions where relations with this faction is equal to $rel
     * @param string $rel
     * @return Faction[]
     */
    public function getFactionsWhereRelation(string $rel) : array {
        $r = [];
        foreach(Factions::getAll() as $f) {
            if($f === $this) continue;
            if($f->getRelationTo($this) === $rel) $r[] = $f;
        }
        return $r;
    }

    // ----------------------------- //
    // Capturing changes on faction  //
    // ----------------------------- //

    /**
     * Saving Faction properties on runtime whenever they change
     */
    public function save()
    {
        // If plugin is disabled it will be saved already, let's save CPU
        if (FactionsPE::isShuttingDown()) return;
        $this->lastSave = time();
        DataProvider::get()->saveFactionData($this, true);
    }

    /**
     * Checks if this player is member of this faction
     * @deprecated
     * @param IFPlayer $player
     * @return BOOL
     */
    public function verifyMember(IFPlayer $player) : BOOL
    {
        if($player->getFactionId() !== $this->id) return false;
        return true; # TODO
    }

    public function __toString(){
        return "{$this->id} ({$this->getName()})";
    }
    
    public function __toArray() {
        # TODO
    }

}
