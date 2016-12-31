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
use pocketmine\Player;

use factions\manager\Members;
use factions\manager\Plots;
use factions\entity\Plot;

class Collections {
	
	// ASCII Map
    CONST MAP_WIDTH = 48;
    CONST MAP_HEIGHT = 8;
    CONST MAP_HEIGHT_FULL = 17;
    CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
    CONST MAP_KEY_WILDERNESS = TextFormat::GRAY . "-";
    CONST MAP_KEY_SEPARATOR = TextFormat::AQUA . "+";
    CONST MAP_KEY_OVERFLOW = TextFormat::WHITE . "-" . TextFormat::WHITE; # ::MAGIC?
    CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Factions (>" . 107 . ") on this Map.";

    public static function getMap(Player $observer, int $width, int $height, int $inDegrees) {
        $centerPs = new Plot($observer);
        $map = [];
        $centerFaction = $centerPs->getOwnerFaction();
        $head = TextFormat::GREEN . " (" . $centerPs->getX() . "," . $centerPs->getZ() . ") " . $centerFaction->getName() . " " . TextFormat::WHITE;
        $head = TextFormat::GOLD . str_repeat("_", (($width - strlen($head)) / 2)) . ".[" . $head . TextFormat::GOLD . "]." . str_repeat("_", (($width - strlen($head)) / 2));
        $map[] = $head;
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        $width = $halfWidth * 2 + 1;
        $height = $halfHeight * 2 + 1;
        $topLeftPs = new Plot($centerPs->x + -$halfWidth, $centerPs->z + -$halfHeight);
        // Get the compass
        $asciiCompass = ASCIICompass::getASCIICompass($inDegrees, TextFormat::RED, TextFormat::GOLD);
        // Make room for the list of names
        $height--;
        $fList = [];
        $chrIdx = 0;
        $overflown = false;
        $chars = self::MAP_KEY_CHARS;
        // For each row
        for ($dz = 0; $dz < $height; $dz++) {
            // Draw and add that row
            $row = "";
            for ($dx = 0; $dx < $width; $dx++) {
                if ($dx == $halfWidth && $dz == $halfHeight) {
                    $row .= (self::MAP_KEY_SEPARATOR);
                    continue;
                }
                if (!$overflown && $chrIdx >= strlen(self::MAP_KEY_CHARS)) $overflown = true;
                $herePs = $topLeftPs->add($dx, 0, $dz);
                $hereFaction = Plots::getFactionAt(Plots::fromHash($herePs->x . ":" . $herePs->z . ":" . $observer->getLevel()->getName()));
                $contains = in_array($hereFaction, $fList, true);
                if ($hereFaction->isNone()) {
                    $row .= self::MAP_KEY_WILDERNESS;
                } elseif (!$contains && $overflown) {
                    $row .= self::MAP_KEY_OVERFLOW;
                } else {
                    if (!$contains) $fList[$chars{$chrIdx++}] = $hereFaction;;
                    $fchar = array_search($hereFaction, $fList);
                    $row .= $hereFaction->getColorTo(Members::get($observer)) . $fchar;
                }
            }
            $line = $row; // ... ---------------
            // Add the compass
            if ($dz == 0) $line = $asciiCompass[0] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            if ($dz == 1) $line = $asciiCompass[1] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            if ($dz == 2) $line = $asciiCompass[2] . "" . substr($row, 3 * strlen(self::MAP_KEY_SEPARATOR));
            $map[] = $line;
        }
        $fRow = "";
        foreach ($fList as $char => $faction) {
            $fRow .= $faction->getColorTo(Members::get($observer)) . $char . ": " . $faction->getName() . " ";
        }
        if ($overflown) $fRow .= self::MAP_OVERFLOW_MESSAGE;
        $fRow = trim($fRow);
        $map[] = $fRow;
        return $map;
    }


    public static function table(array $strings, int $cols) : array {
        $ret = [];
        $row = "";
        $count = 0;
        $i = 0;
        foreach ($strings as $string) {
            $row .= $string;
            $count++;
            $i++;
            if ($i < count($string) && $count != $cols) {
                $row .= Text::parse(" <i>| ");
            } else {
                $ret[] = $row;
                $row = "";
                $count = 0;
            }
        }
        return $ret;
    }

}