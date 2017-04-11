<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.11.4
 * Time: 23:45
 */

namespace factions\command;


use dominate\Command;
use dominate\parameter\Parameter;
use factions\command\parameter\FactionParameter;
use factions\entity\Faction;
use factions\event\faction\FactionRenameEvent;
use factions\manager\Members;
use factions\manager\Permissions;
use factions\permission\Permission;
use localizer\Localizer;
use pocketmine\command\CommandSender;

class Name extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("name", Parameter::TYPE_STRING));
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        /** @var \factions\entity\Faction $faction */
        /** @var string $name */
        $name = $this->getArgument(0);
        $faction = $this->getArgument(1);
        $msender = Members::get($sender);

        if(!$msender->isOverriding()) {
            if (!$faction->isPermitted($msender->getRelationTo($faction), $p = Permissions::getById(Permission::NAME))) {
                return ["faction-permission-error", ["perm_desc" => $p->getDescription()]];
            }
        }

        $errors = Faction::validateName($name);
        if (($c = count($errors)) > 0) {
            $sender->sendMessage(Localizer::translatable('invalid-faction-name', [
                "name" => $name,
                "count" => $c
            ]));
            foreach ($errors as $n => $error) {
                $sender->sendMessage(Localizer::translatable('invalid-faction-name-error', [
                    "error" => $error,
                    "n" => $n + 1
                ]));
            }
            return true;
        }

        $event = new FactionRenameEvent($faction, $name);
        $this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
        if ($event->isCancelled()) return false;
        $name = $event->getNewName();
        $faction->setName($name);

        $faction->sendMessage(Localizer::translatable("faction-renamed", [
            "by" => $msender->getDisplayName(),
            "name" => $name
        ]));
        if ($faction !== $msender->getFaction()) {
            $sender->sendMessage(Localizer::trans("you-renamed-faction", [
                "old-name" => $event->getOldName(),
                "new-name" => $event->getNewName()
            ]));
        }
        return true;
    }

}
