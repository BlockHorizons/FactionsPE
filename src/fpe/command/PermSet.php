<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use dominate\parameter\Parameter;
use fpe\command\parameter\FactionParameter;
use fpe\command\parameter\PermissionParameter;
use fpe\command\parameter\RelationParameter;
use fpe\entity\Faction;
use fpe\event\faction\FactionPermissionChangeEvent;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\permission\Permission;
use fpe\relation\Relation;
use fpe\utils\Gameplay;
use fpe\utils\Text;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class PermSet extends Command
{

    public function setup()
    {
        $this->addParameter(new PermissionParameter("perm", PermissionParameter::ONE));
        $this->addParameter(new RelationParameter("rel", RelationParameter::ONE));
        $this->addParameter(new Parameter("yes/no", Parameter::TYPE_BOOLEAN));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {

        $member = Members::get($sender);
        /** @var Perm $perm */
        $perm = $this->getArgument(0);
        /** @var string $rel */
        $rel = $this->getArgument(1);
        /** @var bool $val */
        $val = $this->getArgument(2);
        /** @var Faction $faction */
        $faction = $this->getArgument(3);

        if ($faction->isNone() and !$member->isOverriding()) {
            $sender->sendMessage(Localizer::translatable("cant-modify-special-faction-perms"));
            return true;
        }

        if (!($p = Permissions::getById(Permission::PERMS))->has($member, $faction)) {
            $sender->sendMessage(Localizer::translatable("requirement.faction-permission-error", ["perm_desc" => $p->getDescription()]));
            return true;
        }

        if (!$member->isOverriding()) {
            $sender->sendMessage(Localizer::translatable("perm-not-editable", [$perm->getName()]));
            return true;
        }

        $event = new FactionPermissionChangeEvent($member, $faction, $perm, $rel, $val);
        FactionsPE::get()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) return true;

        $newVal = $event->getNewValue();

        // no change
        if ($faction->isPermitted($rel, $perm) === $newVal) {
            $sender->sendMessage(Localizer::translatable("perm-no-change", [$faction->getName(), $perm->getDescription(), Localizer::translatable($newVal ? "<g>YES" : "<b>NOO"), Relation::getColor($rel) . $rel]));
            return true;
        }

        // Apply
        $faction->setRelationPermitted($perm, $rel, $newVal);
        if ($perm === Permissions::getById(Permission::PERMS) && in_array(Relation::LEADER, Permissions::getById(Permission::PERMS)->getStandard(), true)) {
            $faction->setRelationPermitted(Permissions::getById(Permission::PERMS), Relation::LEADER, true);
        }

        $messages = [];
        $messages[] = Text::titleize(Localizer::trans("perm-for", [$faction->getName()]));
        $messages[] = Permission::getStateHeaders();
        $messages[] = Text::parse($perm->getStateInfo($faction->getPermitted($perm), true));

        foreach ($messages as $message) $sender->sendMessage($message);

        $recipients = $faction->getOnlineMembers();
        unset($recipients[array_search($member, $recipients)]);
        if (Gameplay::get('log.perm-change', true)) $recipients[] = Members::get("CONSOLE");
        foreach ($recipients as $p) {
            $p->sendMessage(Localizer::translatable("player-set-faction-perm", [$member->getDisplayName(), $faction->getName()]));
        }
        return true;
    }
}