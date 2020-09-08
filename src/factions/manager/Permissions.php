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

namespace factions\manager;

use factions\FactionsPE;
use factions\permission\Permission;
use factions\relation\Relation;
use localizer\Localizer;
use localizer\Translatable;

final class Permissions
{

    #
    # PocketMine permission nodes
    #
    const ACCESS = "factions.access";
    const ACCESS_VIEW = "factions.access.view";
    const ACCESS_PLAYER = "factions.access.player";
    const ACCESS_FACTION = "factions.access.faction";
    const OVERRIDE = "factions.override";
    const CHAT = "factions.chat";
    const CLAIM = "factions.claim";
    const CLAIM_ONE = "factions.claim.one";
    const CLAIM_AUTO = "factions.claim.auto";
    const CLAIM_FILL = "factions.claim.fill";
    const CLAIM_SQUARE = "factions.claim.square";
    const CLAIM_CIRCLE = "factions.claim.circle";
    const CLAIM_ALL = "factions.claim.all";
    const CLOSE = "factions.close";
    const CREATE = "factions.create";
    const DEMOTE = "factions.rank.demote";
    const DESCRIPTION = "factions.description";
    const DISBAND = "factions.disband";
    const EXPANSIONS = "factions.expansions";
    const FACTION = "factions.faction";
    const FLAG = "factions.flag";
    const FLAG_LIST = "factions.flag.list";
    const FLAG_SET = "factions.flag.set";
    const FLAG_SHOW = "factions.flag.show";
    const HELP = "factions.help";
    const HUD = "factions.hud"; # alphabet order -.-
    const HOME = "factions.home";
    const INFO = "factions.info";
    const INVITE = "factions.invite";
    const INVITE_LIST = "factions.invite.list";
    const INVITE_LIST_OTHER = "factions.invite.list.other";
    const INVITE_ADD = "factions.invite.add";
    const INVITE_REMOVE = "factions.invite.remove";
    const JOIN = "factions.join";
    const JOIN_OTHERS = "factions.join.others";
    const KICK = "factions.kick";
    const LEAVE = "factions.leave";
    const LEADER = "factions.leader"; # Lang
    const LIST = "factions.list";
    const MAIN = "factions.main";
    const MAP = "factions.map";
    const MONEY = "factions.money";
    const MONEY_BALANCE = "factions.money.balance";
    const MONEY_BALANCE_ANY = "factions.money.balance.any";
    const MONEY_DEPOSIT = "factions.money.deposit";
    const MONEY_F2F = "factions.money.f2f";
    const MONEY_F2P = "factions.f2p";
    const MONEY_P2F = "factions.p2f";
    const MONEY_WITHDRAW = "factions.money.withdraw";
    const MOTD = "factions.motd";
    const OPEN = "factions.open";
    const PERM = "factions.perm";
    const PERM_LIST = "factions.perm.list";
    const PERM_SET = "factions.perm.set";
    const PERM_SHOW = "factions.perm.show";
    const PROMOTE = "factions.rank.promote";
    const PLAYER = "factions.player";
    const POWERBOOST = "factions.powerboost";
    const RANK = "factions.rank";
    const RANK_SHOW = "factions.rank.show";
    const RANK_ACTION = "factions.rank.action";
    const RELATION = "factions.relation";
    const RELATION_SET = "factions.relation.set";
    const RELATION_LIST = "factions.relation.list";
    const RELATION_WISHES = "factions.relation.wishes";
    const RELOAD = "factions.reload";
    const SEECHUNK = "factions.seechunk";
    const SEECHUNKOLD = "factions.seechunkold";
    const SETHOME = "factions.sethome";
    const SETPOWER = "factions.setpower";
    const STATUS = "factions.status";
    const NAME = "factions.name";
    const TITLE = "factions.title";
    const TITLE_COLOR = "factions.title.color";
    const TOP = "factions.top";
    const TERRITORYTITLES = "factions.territorytitles";
    const UNCLAIM = "factions.unclaim";
    const UNCLAIM_ONE = "factions.unclaim.one";
    const UNCLAIM_AUTO = "factions.unclaim.auto";
    const UNCLAIM_FILL = "factions.unclaim.fill";
    const UNCLAIM_SQUARE = "factions.unclaim.square";
    const UNCLAIM_CIRCLE = "factions.unclaim.circle";
    const UNCLAIM_ALL = "factions.unclaim.all";
    const UNSETHOME = "factions.unsethome";
    const UNSTUCK = "factions.unstuck";
    const VERSION = "factions.version";

    /*
     * ----------------------------------------------------------
     * PERMISSION STORAGE
     * ----------------------------------------------------------
     */

    /** @var Permission[] */
    private static $permissions = [];

    public static function detach(Permission $perm)
    {
        if (self::contains($perm)) unset(self::$permissions[$perm->getId()]);
    }

    public static function contains(Permission $perm): bool
    {
        return isset(self::$permissions[$perm->getId()]);
    }

    public static function flush() {
        self::$permissions = [];
    }

    /**
     * Creates a Permissions
     */
    public static function init()
    {
        $perms = [
            Permission::BUILD => [
                Permission::PRIORITY_BUILD, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER], true, true, true
            ],
            Permission::PAINBUILD => [
                Permission::PRIORITY_PAINBUILD, [], true, true, true
            ],
            Permission::DOOR => [
                Permission::PRIORITY_DOOR, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true
            ],
            Permission::BUTTON => [
                Permission::PRIORITY_BUTTON, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true
            ],
            Permission::LEVER => [
                Permission::PRIORITY_LEVER, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], true, true, true
            ],
            Permission::CONTAINER => [
                Permission::PRIORITY_CONTAINER, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER], true, true, true
            ],
            Permission::NAME => [
                Permission::PRIORITY_NAME, [Relation::LEADER], false, true, true
            ],
            Permission::DESC => [
                Permission::PRIORITY_DESC, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::MOTD => [
                Permission::PRIORITY_MOTD, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::INVITE => [
                Permission::PRIORITY_INVITE, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::KICK => [
                Permission::PRIORITY_KICK, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::TITLE => [
                Permission::PRIORITY_TITLE, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::HOME => [
                Permission::PRIORITY_HOME, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], false, true, true
            ],
            Permission::STATUS => [
                Permission::PRIORITY_STATUS, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::SETHOME => [
                Permission::PRIORITY_SETHOME, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::DEPOSIT => [
                Permission::PRIORITY_DEPOSIT, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY, Relation::TRUCE, Relation::NEUTRAL, Relation::ENEMY], false, false, false
            ],
            Permission::WITHDRAW => [
                Permission::PRIORITY_WITHDRAW, [Relation::LEADER], false, true, true
            ],
            Permission::TERRITORY => [
                Permission::PRIORITY_TERRITORY, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::ACCESS => [
                Permission::PRIORITY_ACCESS, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::CLAIMNEAR => [
                Permission::PRIORITY_CLAIMNEAR, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER, Relation::RECRUIT, Relation::ALLY], false, false, false
            ],
            Permission::RELATION => [
                Permission::PRIORITY_RELATION, [Relation::LEADER, Relation::OFFICER], false, true, true
            ],
            Permission::DISBAND => [
                Permission::PRIORITY_DISBAND, [Relation::LEADER], false, true, true
            ],
            Permission::PERMS => [
                Permission::PRIORITY_PERMS, [Relation::LEADER], false, true, true
            ],
            Permission::FLAGS => [
                Permission::PRIORITY_FLAGS, [Relation::LEADER], false, true, true
            ]
        ];
        foreach ($perms as $perm => $data) {
            if (self::getById($perm) instanceof Permission) {
                continue;
            }
            Permissions::create($data[0], $perm, $perm, Localizer::translatable("permission." . $perm), $data[1], $data[2], $data[3], $data[4]);

        }
    }

    /**
     * @param int $priority
     * @param string $id
     * @param string $name
     * @param Translatable $desc
     * @param array $standard
     * @param bool $territory
     * @param bool $editable
     * @param bool $visible
     * @return Permission
     * @throws \Exception
     */
    public static function create(int $priority, string $id, string $name, Translatable $desc, array $standard, bool $territory, bool $editable, bool $visible): Permission
    {
        if (self::getById($id) instanceof Permission) {
            throw new \Exception("Permission with id=$id has been already registered");
        }
        $ret = new Permission($id, $priority, $name, $desc, $standard, $territory, $editable, $visible);
        self::attach($ret);
        return $ret;
    }

    /**
     * @param string $id
     * @return Permission|null
     */
    public static function getById(string $id)
    {
        foreach (self::$permissions as $p) {
            if ($p->getId() === strtolower($id)) {
                return $p;
            }
        }
        return null;
    }

    public static function attach(Permission $perm)
    {
        if (!self::contains($perm)) self::$permissions[$perm->getId()] = $perm;
    }

    public static function saveAll()
    {
        $dataProvider = FactionsPE::get()->getDataProvider();
        if($dataProvider === null) {
            FactionsPE::get()->getLogger()->critical("Failed to save Permissions to specified data provider. Saving to backup file...");
            yaml_emit_file(FactionsPE::get()->getDataFolder() . "permissions_backup.yml", self::getAll());
        } else {
            $dataProvider->savePermissions(self::getAll());
        }
    }

    /**
     * @return Permission[]
     */
    public static function getAll(): array
    {
        return self::$permissions;
    }

}
