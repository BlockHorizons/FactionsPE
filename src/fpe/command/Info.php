<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use dominate\Command;
use fpe\command\parameter\FactionParameter;
use fpe\engine\ChatEngine;
use fpe\entity\Faction;
use fpe\manager\Members;
use fpe\relation\Relation as REL;
use fpe\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class Info extends Command
{

    public function setup()
    {
        // Parameters
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        // Args
        /** @var Faction $faction */
        $faction = $this->getArgument(0);
        $member = Members::get($sender);

        // Collect data
        $id = $faction->getId();
        $description = $faction->getDescription();
        $age = $faction->getAge();
        $flags = $faction->getFlags();
        $power = [
            "Land" => $faction->getLandCount(),
            "Power" => $faction->getPower(),
            "Maxpower" => $faction->getPowerMax()
        ];
        $f = "";
        foreach ($flags as $flag => $value) {
            $f .= ($value ? "<green>" : "<red>") . "$flag <yellow>| ";
        }
        $flags = rtrim($f, "| ");

        $relations = [
            REL::ALLY => $faction->getFactionsWhereRelation(REL::ALLY),
            REL::TRUCE => $faction->getFactionsWhereRelation(REL::TRUCE),
            REL::ENEMY => $faction->getFactionsWhereRelation(REL::ENEMY)
        ];
        $title = Text::titleize("Faction " . $member->getColorTo($faction) . $faction->getName());

        // Format and send
        $member->sendMessage($title);
        $member->sendMessage(Text::parse("<gold>ID: <yellow>" . $id));
        $member->sendMessage(Text::parse("<gold>Description: <yellow>" . $description));
        $member->sendMessage(Text::parse("<gold>Age: <purple>" . Text::time_elapsed($age)));
        $member->sendMessage(Text::parse("<gold>Flags: " . $flags));
        $member->sendMessage(Text::parse("<gold>" . implode(TextFormat::YELLOW . " / ", array_keys($power)) . ": <yellow>" . implode(TextFormat::YELLOW . "/", array_values($power))));
        foreach ($relations as $rel => $factions) {
            $member->sendMessage(Text::parse("<gold>Relation " . REL::getColor($rel) . ucfirst($rel) . " <gold>(" . count($factions) . "):"));
            if (empty($factions)) {
                $member->sendMessage(Text::parse("<gray>none"));
            } else {
                $member->sendMessage(Text::parse(implode(" ", array_map(function ($f) {
                    return $f->getName();
                }, $factions))));
            }
        }
        $member->sendMessage(Text::parse("<yellow>Followers Online (" . count($faction->getOnlineMembers()) . "):"));
        $members = [];
        foreach ($faction->getOnlineMembers() as $m) {
            $members[] = "<green>" . ChatEngine::getBadge($m->getRole()) . "<rose>" . $faction->getName() . " <green>" . $m->getDisplayName();
        }
        if (empty($members)) {
            $members[] = "<gray>none";
        }
        $member->sendMessage(Text::parse(rtrim(implode(', ', $members), ', ')));
        $member->sendMessage(Text::parse("<yellow>Followers Offline (" . count($faction->getOfflineMembers()) . "):"));
        $members = [];
        foreach ($faction->getOfflineMembers() as $m) {
            $members[] = "<green>" . ChatEngine::getBadge($m->getRole()) . "<rose>" . $faction->getName() . " <green>" . $m->getDisplayName();
        }
        // Text::getRelationColor(Relation::getRelationOfThatToMe($m, $member)) # Should I add this instead of showing
        # All factions red?
        if (empty($members)) {
            $members[] = "<gray>none";
        }
        $member->sendMessage(Text::parse(rtrim(implode(', ', $members), ', ')));


        return true;
    }

}
