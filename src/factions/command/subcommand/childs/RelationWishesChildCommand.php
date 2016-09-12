<?php
namespace factions\command\subcommand\childs;


use evalcore\command\Command;
use factions\FactionsPE;
use pocketmine\command\CommandSender;

class RelationWishesChildCommand extends Command
{

    /**
     * RelationWishesChildCommand constructor.
     * @param FactionsPE $plugin
     */
    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "wishes", "See all relation wishes for faction", FactionsPE::RELATION_WISHES, ["w"]);

        //$this->addRequirement(self::REQ_NOT_MORE_THAN_ARGS, 1);
    }

    public function execute(CommandSender $sender, $label, array $args) : BOOl
    {
        if (parent::execute($sender, $label, $args) === false) {
            return true;
        }

        echo "Relation -> " . $this->getName();
        return true;
    }
}