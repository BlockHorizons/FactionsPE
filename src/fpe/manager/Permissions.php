<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\manager;

use Exception;
use fpe\FactionsPE;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\localizer\Localizer;
use fpe\localizer\Translatable;

final class Permissions
{

    #
    # PocketMine permission nodes
    #
    const ACCESS = "factionspe.access";
    const ACCESS_VIEW = "factionspe.access.view";
    const ACCESS_PLAYER = "factionspe.access.player";
    const ACCESS_FACTION = "factionspe.access.faction";
    const OVERRIDE = "factionspe.override";
    const CHAT = "factionspe.chat";
    const CLAIM = "factionspe.claim";
    const CLAIM_ONE = "factionspe.claim.one";
    const CLAIM_AUTO = "factionspe.claim.auto";
    const CLAIM_FILL = "factionspe.claim.fill";
    const CLAIM_SQUARE = "factionspe.claim.square";
    const CLAIM_CIRCLE = "factionspe.claim.circle";
    const CLAIM_ALL = "factionspe.claim.all";
    const CLOSE = "factionspe.close";
    const CREATE = "factionspe.create";
    const DEMOTE = "factionspe.rank.demote";
    const DESCRIPTION = "factionspe.description";
    const DISBAND = "factionspe.disband";
    const EXPANSIONS = "factionspe.expansions";
    const FACTION = "factionspe.faction";
    const FLAG = "factionspe.flag";
    const FLAG_LIST = "factionspe.flag.list";
    const FLAG_SET = "factionspe.flag.set";
    const FLAG_SHOW = "factionspe.flag.show";
    const FLY = "factionspe.fly";
    const HELP = "factionspe.help";
    const HUD = "factionspe.hud"; # alphabet order -.-
    const HOME = "factionspe.home";
    const INFO = "factionspe.info";
    const INVITE = "factionspe.invite";
    const INVITE_LIST = "factionspe.invite.list";
    const INVITE_LIST_OTHER = "factionspe.invite.list.other";
    const INVITE_ADD = "factionspe.invite.add";
    const INVITE_REMOVE = "factionspe.invite.remove";
    const JOIN = "factionspe.join";
    const JOIN_OTHERS = "factionspe.join.others";
    const KICK = "factionspe.kick";
    const LEAVE = "factionspe.leave";
    const LEADER = "factionspe.leader"; # Lang
    const LIST = "factionspe.list";
    const MAIN = "factionspe.main";
    const MAP = "factionspe.map";
    const MONEY = "factionspe.money";
    const MONEY_BALANCE = "factionspe.money.balance";
    const MONEY_BALANCE_ANY = "factionspe.money.balance.any";
    const MONEY_DEPOSIT = "factionspe.money.deposit";
    const MONEY_F2F = "factionspe.money.f2f";
    const MONEY_F2P = "factionspe.f2p";
    const MONEY_P2F = "factionspe.p2f";
    const MONEY_WITHDRAW = "factionspe.money.withdraw";
    const MOTD = "factionspe.motd";
    const OPEN = "factionspe.open";
    const PERM = "factionspe.perm";
    const PERM_LIST = "factionspe.perm.list";
    const PERM_SET = "factionspe.perm.set";
    const PERM_SHOW = "factionspe.perm.show";
    const PROMOTE = "factionspe.rank.promote";
    const PLAYER = "factionspe.player";
    const POWERBOOST = "factionspe.powerboost";
    const RANK = "factionspe.rank";
    const RANK_SHOW = "factionspe.rank.show";
    const RANK_ACTION = "factionspe.rank.action";
    const RELATION = "factionspe.relation";
    const RELATION_SET = "factionspe.relation.set";
    const RELATION_LIST = "factionspe.relation.list";
    const RELATION_WISHES = "factionspe.relation.wishes";
    const RELOAD = "factionspe.reload";
    const SEECHUNK = "factionspe.seechunk";
    const SEECHUNKOLD = "factionspe.seechunkold";
    const SETHOME = "factionspe.sethome";
    const SETPOWER = "factionspe.setpower";
    const STATUS = "factionspe.status";
    const NAME = "factionspe.name";
    const TITLE = "factionspe.title";
    const TITLE_COLOR = "factionspe.title.color";
    const TOP = "factionspe.top";
    const TERRITORYTITLES = "factionspe.territorytitles";
    const UNCLAIM = "factionspe.unclaim";
    const UNCLAIM_ONE = "factionspe.unclaim.one";
    const UNCLAIM_AUTO = "factionspe.unclaim.auto";
    const UNCLAIM_FILL = "factionspe.unclaim.fill";
    const UNCLAIM_SQUARE = "factionspe.unclaim.square";
    const UNCLAIM_CIRCLE = "factionspe.unclaim.circle";
    const UNCLAIM_ALL = "factionspe.unclaim.all";
    const UNSETHOME = "factionspe.unsethome";
    const UNSTUCK = "factionspe.unstuck";
    const VERSION = "factionspe.version";

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
            ],
            Permission::FLY => [
                Permission::PRIORITY_FLY, [Relation::LEADER, Relation::OFFICER, Relation::MEMBER], false, true, true
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
     * @throws Exception
     */
    public static function create(int $priority, string $id, string $name, Translatable $desc, array $standard, bool $territory, bool $editable, bool $visible): Permission
    {
        if (self::getById($id) instanceof Permission) {
            throw new Exception("Permission with id=$id has been already registered");
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
