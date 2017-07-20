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

namespace factions\manager;

use factions\FactionsPE;
use factions\flag\Flag;
use localizer\Localizer;
use localizer\Translatable;

class Flags
{

    /** @var Flag[] */
    private static $flags = [];

    public static function detach(Flag $flag)
    {
        if (self::contains($flag))
            unset(self::$flags[$flag->getId()]);
    }

    public static function saveAll()
    {
        if($p = FactionsPE::get()->getDataProvider()) {
            $p->saveFlags(self::getAll());
        }
    }

    public static function getAll(): array
    {
        return self::$flags;
    }

    public static function init()
    {
        // id, priority, name, desc, descYes, descNo, standard, editable, visible
        $flags = [
            Flag::OPEN => [
                Flag::PRIORITY_OPEN, true, true, true
            ],
            Flag::PERMANENT => [
                Flag::PRIORITY_PERMANENT, false, false, true
            ],
            Flag::PEACEFUL => [
                Flag::PRIORITY_PEACEFUL, false, false, true
            ],
            Flag::INFINITY_POWER => [
                Flag::PRIORITY_INFINITY_POWER, false, false, true
            ],
            Flag::POWER_LOSS => [
                Flag::PRIORITY_POWER_LOSS, true, false, true
            ],
            Flag::PVP => [
                Flag::PRIORITY_PVP, true, false, true
            ],
            Flag::FRIENDLY_FIRE => [
                Flag::PRIORITY_FRIENDLY_FIRE, false, false, true
            ],
            Flag::MONSTERS => [
                Flag::PRIORITY_MONSTERS, false, true, true
            ],
            Flag::ANIMALS => [
                Flag::PRIORITY_ANIMALS, true, true, true
            ],
            Flag::EXPLOSIONS => [
                Flag::PRIORITY_EXPLOSIONS, true, false, true
            ],
            Flag::OFFLINE_EXPLOSIONS => [
                Flag::PRIORITY_OFFLINE_EXPLOSIONS, false, false, true
            ],
            Flag::FIRE_SPREAD => [
                Flag::PRIORITY_FIRE_SPREAD, true, false, true
            ],
            Flag::ENDER_GRIEF => [
                Flag::PRIORITY_ENDER_GRIEF, false, false, true
            ],
            Flag::ZOMBIE_GRIEF => [
                Flag::PRIORITY_ZOMBIE_GRIEF, false, false, true
            ]
        ];
        foreach ($flags as $id => $flag) {
            if (self::getById($id) instanceof Flag) {
                continue;
            }
            $desc = Localizer::translatable("flag.$id-desc");
            $descYes = Localizer::translatable("flag.$id-desc-yes");
            $descNo = Localizer::translatable("flag.$id-desc-no");
            self::create($id, $flag[0], $id, $desc, $descYes, $descNo, $flag[1], $flag[2], $flag[3]);
        }
    }

    public static function getById(string $id)
    {
        foreach (self::$flags as $flag) {
            if ($flag->getId() === strtolower(trim($id))) return $flag;
        }
        return null;
    }

    public static function create(string $id, int $priority, string $name, Translatable $desc, Translatable $descYes, Translatable $descNo, bool $standard, bool $editable, bool $visible)
    {
        if (self::getById($id) instanceof Flag) {
            throw new \Exception("Flag with id=$id has been already registered");
        }
        self::attach(new Flag($id, $priority, $name, $desc, $descYes, $descNo, $standard, $editable, $visible));
        return self::getById($id);
    }

    public static function attach(Flag $flag)
    {
        if (!self::contains($flag))
            self::$flags[$flag->getId()] = $flag;
    }

    public static function contains(Flag $flag): bool
    {
        return isset(self::$flags[$flag->getId()]);
    }

    /**
     * Detaches all flags
     */
    public static function flush()
    {
        self::$flags = [];
    }

}