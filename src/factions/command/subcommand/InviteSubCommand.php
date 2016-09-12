<?php
namespace factions\command\subcommand;

use evalcore\command\Command;
use evalcore\command\parameter\Parameter;
use evalcore\command\parameter\type\primitive\TypeString;
use evalcore\requirement\ReqBePlayer;
use factions\command\requirement\ReqHasPerm;
use factions\command\subcommand\childs\InviteAddChild;
use factions\command\subcommand\childs\InviteListChild;
use factions\command\subcommand\childs\InviteRemoveChild;
use factions\entity\Perm;
use factions\FactionsPE;

class InviteSubCommand extends Command
{

    public function __construct(FactionsPE $plugin)
    {
        parent::__construct($plugin, "invite", "Invite someone to your faction", FactionsPE::INVITE, ["inv"]
        );
        $this->addChild(new InviteAddChild($plugin));
        $this->addChild(new InviteListChild($plugin));
        $this->addChild(new InviteRemoveChild($plugin));

        $this->addRequirement(new ReqBePlayer());
        $this->addRequirement(new ReqHasPerm(Perm::getPermById(Perm::INVITE)));

        $this->addParameter(new Parameter("add|remove|list", new TypeString(), false));
    }

}
