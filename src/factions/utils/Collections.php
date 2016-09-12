<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/30/16
 * Time: 4:30 PM
 */

namespace factions\utils;


use factions\entity\Faction;
use factions\entity\FPlayer;
use factions\interfaces\IFPlayer;
use factions\objs\Plots;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Collections
{

    public static function sort(array $items, string $by, string $default) {
        if(empty($items)) return $items;
        // ---------
        // Sorting player list
        // ---------
        if($items[0] instanceof IFPlayer)
        {
            if($by !== "time" &&
               $by !== "power" &&
               $by !== "money"
            ) $by = $default;
            switch ($by)
            {
                default: return $items; break;
                case "time":
                    // sorting by time ??
                    $sorted = [];
                    foreach($items as $player){
                        $sorted[$player->getName()] = $player->getLastPlayed();
                    }
                    sort($sorted, SORT_ASC);
                    $ret = [];
                    var_dump($sorted);
                    foreach($sorted as $key => $time){
                        $ret[$key] = $items[$key];
                    }
                return $ret;
                break;
            }
        }
        return $items;
    }

    public static function getMap(Player $observer, int $width, int $height, int $inDegrees)
    {
        $centerPs = new Vector3($observer->x >> 4, 0, $observer->z >> 4);

        $map = [];

        $centerFaction = Plots::get()->getFactionAt($observer);

        $head = TextFormat::GREEN . " (" . $centerPs->getX() . "," . $centerPs->getZ() . ") " . $centerFaction->getName() . " " . TextFormat::WHITE;
        $head = TextFormat::GOLD . str_repeat("_", (($width - strlen($head)) / 2)) . ".[" . $head . TextFormat::GOLD . "]." . str_repeat("_", (($width - strlen($head)) / 2));

        $map[] = $head;

        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        $width = $halfWidth * 2 + 1;
        $height = $halfHeight * 2 + 1;

        $topLeftPs = new Vector3($centerPs->x + -$halfWidth, 0, $centerPs->z + -$halfHeight);

        // Get the compass
        $asciiCompass = ASCIICompass::getASCIICompass($inDegrees, TextFormat::RED, TextFormat::GOLD);

        // Make room for the list of names
        $height--;

        /** @var Faction[] $fList */
        $fList = array();
        $chrIdx = 0;
        $overflown = false;
        $chars = Constants::MAP_KEY_CHARS;

        // For each row
        for ($dz = 0; $dz < $height; $dz++) {
            // Draw and add that row
            $row = "";
            for ($dx = 0; $dx < $width; $dx++) {
                if ($dx == $halfWidth && $dz == $halfHeight) {
                    $row .= (Constants::MAP_KEY_SEPARATOR);
                    continue;
                }

                if (!$overflown && $chrIdx >= strlen(Constants::MAP_KEY_CHARS)) $overflown = true;
                $herePs = $topLeftPs->add($dx, 0, $dz);
                $hereFaction = Plots::get()->getFactionAt(Plots::fromHash($herePs->x . ":" . $herePs->z . ":" . $observer->getLevel()->getName()));
                $contains = in_array($hereFaction, $fList, true);
                if ($hereFaction->isNone()) {
                    $row .= Constants::MAP_KEY_WILDERNESS;
                } elseif (!$contains && $overflown) {
                    $row .= Constants::MAP_KEY_OVERFLOW;
                } else {
                    if (!$contains) $fList[$chars{$chrIdx++}] = $hereFaction;;
                    $fchar = array_search($hereFaction, $fList);
                    $row .= $hereFaction->getColorTo(FPlayer::get($observer)) . $fchar;
                }
            }

            $line = $row; // ... ---------------

            // Add the compass
            if ($dz == 0) $line = $asciiCompass[0] . "" . substr($row, 3 * strlen(Constants::MAP_KEY_SEPARATOR));
            if ($dz == 1) $line = $asciiCompass[1] . "" . substr($row, 3 * strlen(Constants::MAP_KEY_SEPARATOR));
            if ($dz == 2) $line = $asciiCompass[2] . "" . substr($row, 3 * strlen(Constants::MAP_KEY_SEPARATOR));

            $map[] = $line;
        }

        $fRow = "";
        foreach ($fList as $char => $faction) {
            $fRow .= $faction->getColorTo(FPlayer::get($observer)) . $char . ": " . $faction->getName() . " ";
        }
        if ($overflown) $fRow .= Constants::MAP_OVERFLOW_MESSAGE;
        $fRow = trim($fRow);
        $map[] = $fRow;

        return $map;
    }

    public static function table(array $strings, int $cols) : ARRAY
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