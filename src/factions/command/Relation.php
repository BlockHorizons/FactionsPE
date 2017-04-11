<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\command;

use dominate\Command;
use dominate\parameter\Parameter;
use factions\manager\Permissions;

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