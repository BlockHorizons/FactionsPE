<?php

namespace fpe\command;

use fpe\command\requirement\FactionPermission;
use fpe\command\requirement\FactionRequirement;
use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\dominate\requirement\SimpleRequirement;
use fpe\localizer\Localizer;
use fpe\manager\Members;
use fpe\manager\Permissions;
use fpe\manager\Plots;
use fpe\permission\Permission;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;

class SetHome extends Command
{

    public function setup()
    {
        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionRequirement(FactionRequirement::IN_FACTION));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::SETHOME)));

        $this->addParameter((new Parameter("player|x"))->setDefaultValue(null));
        $this->addParameter((new Parameter("y"))->setDefaultValue(null));
        $this->addParameter((new Parameter("z"))->setDefaultValue(null));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (!$sender instanceof Player) return false;
        $member = Members::get($sender);
        $position = $sender->getPosition();
        if (count($args) === 1) {
            if (($target = $this->plugin->getServer()->getPlayer($args[0])) instanceof Player) {
                $position = $target->getPosition();
            } else {
                $sender->sendMessage(Localizer::translatable('sethome-invalid-player', ["player" => $args[0]]));
                return true;
            }
        } elseif (count($args) === 3) {
            if (!is_numeric($args[0]) || !is_numeric($args[1]) || !is_numeric($args[2])) {
                $this->sendUsage($sender);
                return true;
            }
            $position = new Position($args[0], $args[1], $args[2]);
            if (isset($args[3])) {
                if (($level = $this->getPlugin()->getServer()->getLevelByName($args[3])) instanceof Level) {
                    $position->level = $level;
                } else {
                    $sender->sendMessage(Localizer::translatable('sethome-invalid-level', [$args[3]]));
                    return true;
                }
            } else {
                $position->level = $sender->getLevel();
            }
        }

        $factionHere = Plots::getFactionAt($position);

        if (!$member->getFaction()->isValidHome($position)) {
            $member->sendMessage(Localizer::translatable('sethome-not-here', [$factionHere->getName()]));
            return true;
        }

        $member->getFaction()->setHome($position);
        $member->sendMessage(Localizer::translatable('sethome-success', ["at" =>
            "(" . $position->x . ", " . $position->y . ", " . $position->z . ", " . $position->level->getName() . ")"]));
        return true;
    }
}