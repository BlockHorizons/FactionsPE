<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\utils;

class ASCIICompass
{

    const N = 'N';
    const NE = 'NE';
    const E = 'E';
    const SE = 'SE';
    const S = 'S';
    const SW = 'SW';
    const W = 'W';
    const NW = 'NW';

    const DIAGONAL = [self::NE => "/", self::SE => "/", self::SW => "\\", self::NW => "\\"];

    const FULL = [
        self::N => "North", self::S => "South", self::E => "East", self::W => "West",
        self::NE => "North East", self::SE => "South East", self::SW => "South West", self::NW => "North West"
    ];

    /**
     * @param int $degrees
     * @param string $colorActive
     * @param string $colorDefault
     */
    public static function getASCIICompass(int $degrees, string $colorActive, string $colorDefault): array
    {
        $ret = [];
        $point = self::getCompassPointForDirection($degrees);
        $row = "";
        $row .= ($point === self::NW ? $colorActive : $colorDefault) .self::DIAGONAL[self::NW];
        $row .= ($point === self::N ? $colorActive : $colorDefault) . self::N;
        $row .= ($point === self::NE ? $colorActive : $colorDefault) . self::DIAGONAL[self::NE];
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::W ? $colorActive : $colorDefault) . self::W;
        $row .= $colorDefault . "+";
        $row .= ($point === self::E ? $colorActive : $colorDefault) . self::E;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::SW ? $colorActive : $colorDefault) . self::DIAGONAL[self::SW];
        $row .= ($point === self::S ? $colorActive : $colorDefault) . self::S;
        $row .= ($point === self::SE ? $colorActive : $colorDefault) . self::DIAGONAL[self::SE];
        $ret[] = $row;
        return $ret;
    }

    /**
     * @param int $degrees
     * @return string|null
     */
    public static function getCompassPointForDirection(int $degrees)
    {
        $degrees = ($degrees - 180) % 360;
        if ($degrees < 0)
            $degrees += 360;
        if (0 <= $degrees && $degrees < 22.5)
            return self::N;
        elseif (22.5 <= $degrees && $degrees < 67.5)
            return self::NE;
        elseif (67.5 <= $degrees && $degrees < 112.5)
            return self::E;
        elseif (112.5 <= $degrees && $degrees < 157.5)
            return self::SE;
        elseif (157.5 <= $degrees && $degrees < 202.5)
            return self::S;
        elseif (202.5 <= $degrees && $degrees < 247.5)
            return self::SW;
        elseif (247.5 <= $degrees && $degrees < 292.5)
            return self::W;
        elseif (292.5 <= $degrees && $degrees < 337.5)
            return self::NW;
        elseif (337.5 <= $degrees && $degrees < 360.0)
            return self::N;
        else
            return null;
    }

    public static function getFullDirection(float $degrees) : string
    {
        $point = self::getCompassPointForDirection($degrees);
        return $point ? self::FULL[$point] : "";
    }

}