<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.11.4
 * Time: 23:45
 */

namespace BlockHorizons\FactionsPE\command;


use BlockHorizons\FactionsPE\command\parameter\FactionParameter;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\event\faction\FactionRenameEvent;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
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
        /** @var \BlockHorizons\FactionsPE\entity\Faction $faction */
        /** @var string $name */
        $name = $this->getArgument(0);
        $faction = $this->getArgument(1);
        $msender = Members::get($sender);

        if (!$msender->isOverriding()) {
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
