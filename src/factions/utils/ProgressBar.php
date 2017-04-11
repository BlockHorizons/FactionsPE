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

namespace factions\utils;

use pocketmine\utils\TextFormat;

class ProgressBar
{

    const HEALTH_BAR_CLASSIC = 0x00ec;

    protected $type;
    protected $quota;
    protected $width;
    protected $color = TextFormat::WHITE;

    public function __construct(int $type, int $quota, int $width)
    {
        $this->type = $type;
        $this->quota = $quota;
        $this->width = $width;
    }

    // --------------------------- //
    // GETTERS & SETTERS
    // --------------------------- //

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuota()
    {
        return $this->quota;
    }

    /**
     * @param int $quota
     * @return ProgressBar
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return ProgressBar
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        switch ($this->getType()) {
            case self::HEALTH_BAR_CLASSIC:
                #
                # [======          ]
                #
                $ret = $this->color . "[";
                $left = $this->width - $this->quota;
                $ret .= TextFormat::RED . str_repeat("=", $this->quota) . str_repeat(" ", $left) . $this->color . "]";
                return $ret;
                break;
            default:
                break;
        }
        return "";
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    // ---------------------------------------- //
    // RENDER
    // ---------------------------------------- //

    /**
     * @param int $type
     * @return ProgressBar
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}