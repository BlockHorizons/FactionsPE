<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/20/16
 * Time: 3:54 PM
 */

namespace factions\command\requirement;


use evalcore\requirement\Requirement;
use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\entity\Perm;
use pocketmine\command\CommandSender;

class ReqHasPerm extends Requirement
{
    /**
     * @var Perm|null
     */
    public $permission = null;

    /**
     * @var Faction|null
     */
    public $faction = null;
    
    public function __construct(Perm $perm, Faction $faction = null)
    {
        parent::__construct("has-perm");
        $this->permission = $perm;
        $this->faction = $faction;
    }
    
    public function isMet(CommandSender $sender, array $args, $silent = false) : BOOL {
        $fsender = FPlayer::get($sender);
        $faction = $this->faction === null ? $fsender->getFaction() : $this->faction;
        $ret = $this->permission->has($fsender, $faction);
        if(!$ret and !$silent) $sender->sendMessage($this->createDeniedMessage($sender));
        return $ret;
    }

}