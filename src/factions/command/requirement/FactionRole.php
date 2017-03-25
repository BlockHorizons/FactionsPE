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

namespace factions\command\requirement;

use dominate\requirement\Requirement;
use factions\manager\Members;
use localizer\Translatable;
use pocketmine\command\CommandSender;

class FactionRole extends Requirement
{

    /** @var string */
    protected $role;

    public function __construct(string $role)
    {
        $this->role = $role;
        $this->type = self::class;
    }

    public function hasMet(CommandSender $sender, $silent = false): bool
    {
        return Members::get($sender)->getRole() === $this->role;
    }

    public function createErrorMessage(CommandSender $sender = null): Translatable
    {
        return new Translatable("requirement.role-error", [
            "role" => $this->role
        ]);
    }

}