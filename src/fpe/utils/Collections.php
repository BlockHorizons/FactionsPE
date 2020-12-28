<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\utils;

use fpe\entity\Plot;
use fpe\manager\Members;
use fpe\manager\Plots;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Collections
{

    // ASCII Map
    const MAP_WIDTH = 48;
    const MAP_HEIGHT = 8;
    const MAP_HEIGHT_FULL = 17;
    const MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
    const MAP_KEY_WILDERNESS = TextFormat::GRAY . "-";
    const MAP_KEY_SEPARATOR = TextFormat::AQUA . "+";
    const MAP_KEY_OVERFLOW = TextFormat::WHITE . "-" . TextFormat::WHITE; # ::MAGIC?
    const MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Factions (>" . 107 . ") on this Map.";
    const MAP_KEY_HOME = "H";

    public static function getMap(Player $observer, int $width, int $height, int $inDegrees)
    {
        $centerPs = new Plot($observer);
        $m = Members::get($observer);
        $map = [];
        $centerFaction = $centerPs->getOwnerFaction();
        $head = TextFormat::GREEN . " (" . $centerPs->getX() . "," . $centerPs->getZ() . ") " . $centerFaction->getName() . " " . TextFormat::WHITE;
        $head = Text::titleize($head, self::MAP_WIDTH + 12);
        $map[] = $head;
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        $width = $halfWidth * 2 + 1;
        $height = $halfHeight * 2 + 1;
        $topLeftPs = new Plot($centerPs->x + -$halfWidth, $centerPs->z + -$halfHeight, $observer->getLevel());
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

                # TODO: Rewrite this
                $herePs = new Plot($topLeftPs->x + $dx, $topLeftPs->z + $dz, $topLeftPs->level);
                $hereFaction = $herePs->getOwnerFaction();
                $home = $hereFaction->getHome();
                $contains = in_array($hereFaction, $fList, true);

                if ($home instanceof Position && $m->getFaction() === $hereFaction || $m->isOverriding()) {
                    if ($home->x >> Plots::$CHUNK_SIZE === $herePs->x && $home->z >> Plots::$CHUNK_SIZE === $herePs->z) {
                        $row .= $hereFaction->getColorTo($m) . self::MAP_KEY_HOME;
                    } else {
                        goto draw_char;
                    }
                } else {
                    draw_char:
                    if ($hereFaction->isNone()) {
                        $row .= self::MAP_KEY_WILDERNESS;
                    } elseif (!$contains && $overflown) {
                        $row .= self::MAP_KEY_OVERFLOW;
                    } else {
                        if (!$contains) $fList[$chars[$chrIdx++]] = $hereFaction;
                        $fchar = array_search($hereFaction, $fList);
                        $row .= $hereFaction->getColorTo($m) . $fchar;
                    }
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


    public static function table(array $strings, int $cols): array
    {
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