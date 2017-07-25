<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\entity;

use factions\data\FactionData;
use factions\event\member\MembershipChangeEvent;
use factions\FactionsPE;
use factions\flag\Flag;
use factions\manager\Factions;
use factions\manager\Flags;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\manager\Plots;
use factions\permission\Permission;
use factions\relation\Relation;
use factions\relation\RelationParticipator;
use factions\utils\Gameplay;
use factions\utils\Text;
use localizer\Localizer;
use localizer\Translatable;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Faction extends FactionData implements RelationParticipator
{

    const NONE = "wilderness";
    const SAFEZONE = "safezone";
    const WARZONE = "warzone";
    const WILDERNESS = self::NONE;

    const NAME_NONE = "Wilderness";
    const NAME_SAFEZONE = "Safezone";
    const NAME_WARZONE = "Warzone";
    const NAME_WILDERNESS = self::NAME_NONE;

    const DISBAND_REASON_UNKNOWN = 0;
    const DISBAND_REASON_EMPTY_FACTION = 1;
    const DISBAND_REASON_COMMAND = 2;
    const DISBAND_REASON_PURGE = 3;

    /**
     * Faction constructor
     * @param string $id
     * @param array $data
     */
    public function __construct(string $id, array $data)
    {
        parent::__construct(array_merge(["id" => $id], $data));

        /**
         * You can pass initial player (creator) using "creator" or "members" data created by Factions::createMemberList
         */
        if (isset($data["creator"]) && !$this->getLeader()) {
            $this->members[Relation::LEADER][] = $data["creator"] instanceof IMember ? strtolower(trim($data["creator"]->getName())) : strtolower(trim($data["creator"]));
        }

        if (Gameplay::get('faction.destroy-empty-factions', true) && !$this->isSpecial()) {
            if ($this->isEmpty()) {
                $this->disband(self::DISBAND_REASON_EMPTY_FACTION);
            }
        }
    }


    /*
     * ----------------------------------------------------------
     * STATUS
     * ----------------------------------------------------------
     */

    /**
     * @internal
     */
    private function isEmpty(): bool {
        return empty($this->getMembers());
    }

    /**
     * Returns this Faction's leader, if returned null and this faction isn't special
     * Then this object is considered invalid and must be destroyed or new Leader has to be attached.
     * As invalid faction object in runtime may cause an unexpected behaviour
     *
     * @return IMember|NULL
     */
    public function getLeader()
    {
        $ret = $this->getMembersWhereRole(Relation::LEADER);
        if (empty($ret)) return null;
        return $ret[0];
    }

    public function getMembersWhereRole(string $role): array
    {
        $ret = [];
        foreach ($this->getMembers() as $player) {
            if ($player->getRole() === $role) $ret[] = $player;
        }
        return $ret;
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array
    {
        $r = [];
        foreach ($this->getRawMembers() as $role => $members) {
            foreach ($members as $name) {
                $r[] = Members::get($name, true);
            }
        }
        return $r;
    }

    /*
     * ----------------------------------------------------------
     * DATA
     * ----------------------------------------------------------
     */

    /**
     * Check if faction is special
     */
    public function isSpecial(): bool
    {
        return in_array($this->getId(), [self::NONE, self::WARZONE, self::SAFEZONE], true);
    }

    public function isPermanent(): bool {
        return $this->getFlag(Flag::PERMANENT);
    }

    public function disband($reason = self::DISBAND_REASON_UNKNOWN, $delete = true)
    {
        if ($this->isPermanent()) {
            throw new \LogicException("can not disband permanent faction $this ({$this->getId()})");
        }
        foreach ($this->getOnlineMembers() as $member) {
            $event = new MembershipChangeEvent($member, Factions::getById(Faction::NONE), MembershipChangeEvent::REASON_DISBAND);
            FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
            // This event cannot be cancelled
        }
        if (Gameplay::get("log.faction-disband", true)) {
            $args = [
                "id" => $this->getId(),
                "name" => $this->getName()
            ];
            $msg = Localizer::trans('log.faction-disband-reason-unknown', $args);
            switch ($reason) {
                case self::DISBAND_REASON_EMPTY_FACTION:
                    $msg = Localizer::trans('log.faction-disband-reason-empty', $args);
                    break;
                case self::DISBAND_REASON_PURGE:
                    $msg = Localizer::trans("log.faction-disband-reason-purge", $args);
                    break;
                default:
                    break;
            }
            FactionsPE::get()->getLogger()->notice($msg);
        }
        Factions::detach($this);
        if ($delete)
            FactionsPE::get()->getDataProvider()->deleteFaction($this->getId());
    }

    /*
     * ----------------------------------------------------------
     * HOME
     * ----------------------------------------------------------
     */

    public function getFlag(string $id): bool
    {
        $ret = isset($this->flags[$id]) ? $this->flags[$id] : null;
        if ($ret !== null) return $ret;
        $flag = Flags::getById($id);
        if ($flag === null) throw new \Exception("undefined flag '$id'");
        return $flag->isStandard();
    }

    /**
     * @return IMember[]
     */
    public function getOnlineMembers(): array
    {
        $ret = [];
        foreach ($this->getMembers() as $member) {
            if ($member->isOnline()) $ret[] = $member;
        }
        return $ret;
    }

    /*
     * ----------------------------------------------------------
     * MEMBERS
     * ----------------------------------------------------------
     */

    public static function createId(): string
    {
        do { // Dangerous?
            $id = implode("-", array_values(array_map(function ($el) {
                return implode("", $el);
            }, array_chunk(str_split(md5(mt_rand(0, PHP_INT_MAX)) . md5(mt_rand(0, PHP_INT_MAX))), 4))));
        } while (Factions::getById($id));
        return substr($id, 0, 4 * 4 + 3);
    }

    /**
     * @param string $name
     * @return Translatable[]
     */
    public static function validateName(string $name): array
    {
        $errors = [];
        if (Factions::getByName($name)) {
            $errors[] = Localizer::translatable('faction-name-taken', [$name]);
        }
        if (($l = strlen($name)) > ($m = Gameplay::get('faction-name.max-length', 12))) {
            $errors[] = Localizer::translatable('faction-name-too-long', [$name, $l, "max" => $m]);
        }
        if ($l < ($mi = Gameplay::get('faction-name.min-length', 3))) {
            $errors[] = Localizer::translatable('faction-name-too-short', [$name, $l, "min" => $mi]);
        }
        if (!ctype_alpha($name)) {
            $errors[] = Localizer::translatable('faction-name-not-alpha', [$name, $l]);
        }
        return $errors;
    }

    public function getMotdMessages(): array
    {
        $ret = [];

        $ret[] = Text::titleize($this->getName() . " - Message of the Day");
        $ret[] = Text::parse("@i" . $this->getMotd() ?? " ~");
        $ret[] = "";

        return $ret;
    }

    public function getLastOnline(): int
    {
        $t = 0;
        foreach ($this->getMembers() as $member) {
            if (($l = $member->getLastPlayed()) > $t) $t = $l;
        }
        return $t;
    }

    /**
     * Check if the faction home is in valid position and doesn't
     * flout the Gameplay settings
     */
    public function verifyHome()
    {
        if ($this->home === null) return;
        if ($this->isValidHome($this->home)) return;
        $this->home = null;
        $this->save();
        $this->sendMessage(Localizer::translatable("home-out-of-bounds"));
    }

    public function isValidHome($home): bool
    {
        if ($home === null) return false;
        if (!$home instanceof Position) return false;
        if (!Gameplay::get("home.must-be-in-claimed-territories", true)) return true;
        if (Plots::getFactionAt($home) === $this) return true;
        return false;
    }

    public function sendMessage($message)
    {
        foreach ($this->getOnlineMembers() as $player) {
            $player->sendMessage($message);
        }
    }

    public function setRole(IMember $member, string $rank): bool
    {
        if (!$this->isMember($member)) return false;

        $name = strtolower(trim($member->getName()));
        $members = $this->getRawMembers();

        // No change
        $currentRank = $this->getRole($member);
        if ($rank === $currentRank) return false;

        // Remove from old role
        unset($members[$currentRank][array_search($name, $members[$currentRank])]);

        // Move into new
        $members[$rank][] = $name;

        // Apply
        $this->setRawMembers($members);

        return $this->getRole($member) === $rank;
    }

    public function isMember(IMember $member): bool
    {
        $member = strtolower(trim($member->getName()));
        foreach ($this->members as $members) {
            if (in_array($member, $members)) return true;
        }
        return false;
    }

    public function getRole(IMember $member): string
    {
        $member = strtolower(trim($member->getName()));
        foreach ($this->members as $role => $members) {
            if (in_array($member, $members)) return $role;
        }
        return Relation::RECRUIT;
    }

    /**
     * Will add $member to members list, only few additional checks will be performed
     * use {@link self::join(IMember $member)} instead
     * @param IMember $member
     * @param string $role valid faction rank
     * @return bool
     */
    public function addMember(IMember $member, string $role): bool
    {
        if ($this->isSpecial()) {
            throw new \LogicException("special faction can't have members in it");
        }
        if ($this->isMember($member)) return false;
        if (($f = Factions::getForMember($member))) {
            throw new \InvalidStateException("Can not add new member to faction 
				'{$this->getName()}'. Member '{$member->getName()}' is member of faction '{$f->getName()}'");
        }
        // TODO: Check for player limit
        if ($role === Relation::LEADER) {
            // Promoting new leader, but at first this member must be in this faction
            $this->members[Relation::RECRUIT][] = strtolower(trim($member->getName()));
            $this->promoteNewLeader($member);
        } else {
            if (!Relation::isRankValid($role)) {
                throw new \InvalidArgumentException("\$role[=$role] must be valid faction rank");
            }
            $this->members[$role][] = strtolower(trim($member->getName()));
        }
        return true;
    }

    /**
     * Try to promote new leader or disband if failed
     * @param IMember $leader = null
     */
    public function promoteNewLeader(IMember $leader = null)
    {
        if ($this->isNone()) return;
        if ($this->getFlag(Flag::PERMANENT) && Gameplay::get("faction.disable-permanent-leader-promotion", true)) return;
        if ($leader && !$leader->hasFaction() or $leader && $leader->getFactionId() !== $this->getId()) return;
        $oldLeader = $this->getLeader();
        // get list of officers, or list of normal members if there are no officers
        $replacements = $leader instanceof Member ? [$leader] : $this->getMembersWhereRole(Relation::OFFICER);
        if (empty($replacements)) {
            $replacements = $this->getMembersWhereRole(Relation::MEMBER);
        }
        if (empty($replacements)) {
            // faction leader is the only member; one-man faction
            if ($this->getFlag(Flag::PERMANENT)) {
                if ($oldLeader != null) {
                    // TODO: Where is the logic in this? Why MEMBER? Why not LEADER again? And why not OFFICER or RECRUIT?
                    $oldLeader->setRole(Relation::MEMBER);
                }
                return;
            }
            // no members left and faction isn't permanent, so disband it
            $this->disband(self::DISBAND_REASON_EMPTY_FACTION);
        } else {
            // promote new faction leader
            if ($oldLeader != null) {
                $oldLeader->setRole(Relation::MEMBER);
            }
            $replacements[0]->setRole(Relation::LEADER);
            $this->sendMessage(Localizer::translatable("faction-new-leader", [$oldLeader == null ? "" : $oldLeader->getName(), $replacements[0]->getName()]));
            if (Gameplay::get('log.faction-new-leader', true)) {
                FactionsPE::get()->getLogger()->info(Localizer::trans('log.new-leader', [
                    $this->getName(), $this->getId(), $replacements[0]->getName()
                ]));
            }
        }
    }

    /**
     * Check if faction is wilderness
     */
    public function isNone(): bool
    {
        return $this->getId() === self::NONE;
    }

    public function join(IMember $member, $role = Relation::RECRUIT): bool
    {
		return true;
    }

    public function leave(IMember $member): bool
    {
        if (!$this->isMember($member)) {
            throw new \InvalidArgumentException("\$member[={$member->getName()}] is not member of this faction[=$this]");
        }
        $permanent = $this->getFlag(Flag::PERMANENT);
        if (count($this->getMembers()) > 1) {
            if (!$permanent && $member->getRole() === Relation::LEADER) {
                $member->sendMessage(Localizer::translatable('faction-leave-as-leader'));
                return false;
            }
            if (!Gameplay::get("can-leave-with-negative-power", false) && $this->getPower() < 0) {
                $member->sendMessage(Localizer::translatable('faction-leave-with-negative-power'));
                return false;
            }
        }
        // Event
        $event = new MembershipChangeEvent($member, $this, MembershipChangeEvent::REASON_LEAVE);
        FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) return false;

        // Lets remove player from faction before announcing it, to avoid :facepalm:
        try {

            if (!$this->removeMember($member)) {
                throw new \Exception("failed to remove member from faction for unknown reason");
            }
            $member->resetFactionData();

        } catch (\Exception $e) {
            $member->sendMessage(Localizer::translatable('faction-leave-exception', [$e->getMessage()]));
            return false;
        }

        // Now we can announce
        if ($this->isNormal()) {
            foreach ($this->getOnlineMembers() as $player) {
                $player->sendMessage(Localizer::translatable("member-left-faction", [$member->getDisplayName()]));
            }
            if (Gameplay::get('log.member-leave')) {
                FactionsPE::get()->getLogger()->info(Localizer::trans('log.member-leave', [$member->getName(), $this->getName()]));
            }
        }

        // Disband the faction if necessary
        if ($this->isNormal() && !$permanent && empty($this->getRawMembers())) {
            $this->disband(Faction::DISBAND_REASON_EMPTY_FACTION);
        }
        return true;
    }

    public function getPower(bool $limit = true): int
    {
        if ($this->getFlag(Flag::INFINITY_POWER)) return PHP_INT_MAX;
        $ret = 0;
        foreach ($this->getMembers() as $fplayer) {
            $ret += $fplayer->getPower($limit);
        }
        $ret += $this->getPowerBoost();
        $max = Gameplay::get('max-faction-power', null); # TODO
        if ($max && $max > 0) $ret = min($ret, $max);
        return $ret;
    }

    /**
     * Member::leave() should be called first!
     * @param IMember $member
     * @return bool
     */
    public function removeMember(IMember $member): bool
    {
        if (!$this->isMember($member)) return false;
        $members = $this->members;
        $mir = $members[$this->getRole($member)];
        foreach ($mir as $key => $m) {
            if ($m === strtolower(trim($member->getName()))) {
                unset($mir[$key]);
            }
        }
        $members[$this->getRole($member)] = $mir;
        $this->members = $members;
        return true;
    }

    /**
     * Check if faction is not one of the default ones
     */
    public function isNormal(): bool
    {
        return !$this->isSpecial();
    }

    /*
     * ----------------------------------------------------------
     * INVITATION
     * ----------------------------------------------------------
     */

    public function isAnyMemberOnline(): bool
    {
        return !$this->isAllMembersOffline();
    }

    public function isAllMembersOffline(): bool
    {
        return empty($this->getOnlineMembers());
    }

    /**
     * @param IMember|string
     * @return bool
     */
    public function isInvited($member): bool
    {
        $member = $member instanceof IMember ? $member->getName() : $member;
        return in_array(strtolower(trim($member)), $this->invitedPlayers, true);
    }

    /**
     * @return string[]
     */
    public function getInvitedMembers(): array
    {
        $r = [];
        foreach ($this->getInvitedPlayers() as $name) {
            $r[] = Members::get($name, true);
        }
        return $r;
    }

    public function setInvitedMembers(array $members)
    {
        foreach ($members as $member) {
            $this->invitedPlayers[] = strtolower(trim($member->getName()));
        }
    }

    /*
     * ----------------------------------------------------------
     * FLAGS
     * ----------------------------------------------------------
     */

    /**
     * @param IMember|string $player
     * @param bool $invited
     */
    public function setInvited($player, bool $invited)
    {
        if (!$invited)
            unset($this->invitedPlayers[array_search(strtolower(trim($player instanceof IMember ? $player->getName() : $player)), $this->invitedPlayers)]);
        else
            $this->invitedPlayers[] = strtolower(trim($player instanceof IMember ? $player->getName() : $player));
    }

    /**
     * @return IMember[]
     */
    public function getOnlineInvitedMembers(): array
    {
        $r = [];
        foreach ($this->getInvitedPlayers() as $name) {
            $m = Members::get($name, false);
            if (!$m || !$m->isOnline()) continue;
            $r[] = $m;
        }
        return $r;
    }

    public function setPermissionId(array $target)
    {
        // Detect Nochange
        if ($this->perms === $target) return;
        // Apply
        $this->perms = $target;
    }

    public function isDefaultOpen(): bool
    {
        return Flags::getById(Flag::OPEN)->isStandard();
    }

    public function isOpen(): bool
    {
        return $this->getFlag(Flag::OPEN);
    }

    public function setOpen(bool $open)
    {
        $this->setFlag(Flag::OPEN, $open);
    }

    public function setFlag(string $id, bool $value)
    {
        $this->setFlagId([$id => $value]);
    }

    public function setFlagId(array $target)
    {
        // Detect Nochange
        if ($this->flags === $target) return;
        // Apply
        $this->flags = $target;
    }

    /**
     * @param array string => bool
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param Flag []
     */
    public function setFlags(array $flags)
    {
        $flagIds = [];
        foreach ($flags as $flag) {
            $flagIds[$flag->getId()] = $flag->isStandard();
        }
        $this->flags = array_merge($flagIds, $this->flags);
    }

    /*
     * ----------------------------------------------------------
     * PERMISSIONS
     * ----------------------------------------------------------
     */

    /**
     * Returns true if explosion can occur on this Faction's land
     * @return bool
     */
    public function isExplosionsAllowed(): bool
    {
        $explosions = $this->getFlag(Flag::EXPLOSIONS);
        $offlineexplosions = $this->getFlag(Flag::OFFLINE_EXPLOSIONS);
        if ($explosions && $offlineexplosions) return true;
        if (!$explosions && !$offlineexplosions) return false;
        $online = $this->isConsideredOnline();
        return ($online && $explosions) || (!$online && $offlineexplosions);
    }

    public function isConsideredOnline(): bool
    {
        return !$this->isConsideredOffline();
    }

    public function isConsideredOffline(): bool
    {
        return $this->isAllMembersOffline();
    }

    public function setRelationPermitted(Permission $perm, string $rel, bool $permitted)
    {
        $perms = $this->getPermissions();
        $relations = $this->getPermitted($perm);
        if ($permitted and !$this->isPermitted($perm, $rel)) {
            $relations[] = $rel;
        } else {
            unset($relations[array_search($rel, $relations, true)]);
        }
        $perms[$perm->getId()] = $relations;
        $this->setPermissions($perms);
    }

    public function getPermissions(): array
    {
        $r = [];
        foreach (Permissions::getAll() as $perm) {
            $r[$perm->getId()] = $this->perms[$perm->getId()] ?? $perm->getStandard();
        }
        return $r;
    }

    /**
     * Get array of relations that has Permission
     * @param Permission|string
     * @return array
     */
    public function getPermitted($perm): array
    {
        $id = $perm instanceof Permission ? $perm->getId() : $perm;
        return $this->getPermissions()[$id] ?? [];
    }

    /*
     * ----------------------------------------------------------
     * RELATIONS
     * ----------------------------------------------------------
     */

    /**
     * @param string $rel relation id
     * @param Permission|string $perm
     * @return bool
     */
    public function isPermitted($rel, $perm): bool
    {
        return in_array($rel, $this->getPermitted($perm), true);
    }

    public function setPermissions(array $perms)
    {
        foreach ($perms as $key => $relations) {
            $this->perms[$key] = $relations;
        }
    }

    public function setPermittedRelations(Permission $perm, array $rels)
    {
        $this->setPermissions([$perm->getId() => $rels]);
    }

    /**
     * Returns list of Factions where relations with this faction is equal to $rel
     * @param string $rel
     * @return Faction[]
     */
    public function getFactionsWhereRelation(string $rel): array
    {
        $r = [];
        foreach (Factions::getAll() as $f) {
            if ($f === $this) continue;
            if ($f->getRelationTo($this) === $rel) $r[] = $f;
        }
        return $r;
    }

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false): string
    {
        return Relation::getRelationOfThatToMe($this, $observer, $ignorePeaceful);
    }

    /**
     * @param Faction|string $faction
     * @return string relation id
     * @internal param string $rel
     */
    public function getRelationWish($faction): string
    {
        $fid = $faction instanceof Faction ? $faction->getId() : $faction;
        return $this->relationWishes[$fid] ?? Relation::NEUTRAL;
    }

    /**
     * @param Faction|string $faction
     * @param string $rel
     *
     */
    public function setRelationWish($faction, string $rel)
    {
        $fid = $faction instanceof Faction ? $faction->getId() : $faction;
        if (!$rel || $rel === Relation::NEUTRAL) {
            unset($this->relationWishes[array_search($fid, $this->relationWishes)]);
        } else {
            $this->relationWishes[$fid] = $rel;
        }
    }

    /**
     * @return array string => string (faction id => relation id)
     */
    public function getRelationWishes(): array
    {
        return $this->relationWishes;
    }

    /*
     * ----------------------------------------------------------
     * PLOTS
     * ----------------------------------------------------------
     */

    public function isFriend(RelationParticipator $observer): bool
    {
        return Relation::isFriend($this->getRelationTo($observer));
    }

    public function isEnemy(RelationParticipator $observer): bool
    {
        return Relation::isEnemy($this->getRelationTo($observer));
    }

    public function getColorTo(RelationParticipator $observer): string
    {
        return Relation::getColorOfThatToMe($this, $observer);
    }

    public function getPlotsCountInLevel(Level $level): int
    {
        return count($this->getPlotsInLevel($level));
    }

    public function getPlotsInLevel(Level $level): array
    {
        return Plots::getFactionPlotsInLevel($this, $level);
    }

    /*
     * ----------------------------------------------------------
     * POWER
     * ----------------------------------------------------------
     */

    public function hasLandInflation(): bool
    {
        return ($this->getLandCount() * Gameplay::get("power.per-claim", 5)) > $this->getPower();
    }

    public function getLandCount(): int
    {
        return count($this->getAllPlots());
    }

    public function getAllPlots(): array
    {
        return Plots::getFactionPlots($this);
    }

    public function getPowerMax(): int
    {
        if ($this->getFlag(Flag::INFINITY_POWER)) return $this->getPower();
        $ret = 0;
        foreach ($this->getMembers() as $member) {
            $ret += $member->getPowerMax();
        }
        return $ret;
    }

    public function __toString(): string
    {
        return $this->getName() . " (" . $this->getId() . ")";
    }

}
