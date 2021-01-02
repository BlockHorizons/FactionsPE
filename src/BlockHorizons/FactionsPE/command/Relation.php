<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\manager\Permissions;

class Relation extends Command
{

    public function setup()
    {
        $this->addParameter(new Parameter("set|list|wishes"));

        $this->addChild(new RelationSet($this->getPlugin(), "set", "Set relation wish", Permissions::RELATION_SET));
        $this->addChild(new RelationList($this->getPlugin(), "list", "List faction relations", Permissions::RELATION_LIST));
        $this->addChild(new RelationWishes($this->getPlugin(), "wishes", "List faction relation wishes", Permissions::RELATION_WISHES));
    }

}