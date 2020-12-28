<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command;

use fpe\dominate\Command;
use fpe\dominate\parameter\Parameter;
use fpe\manager\Permissions;

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