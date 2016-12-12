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

use factions\FactionsPE;
use pocketmine\utils\TextFormat;

final class Text
{

    private function __construct() {}
    
    public static function parse(string $text) : string {
        return self::parseColorVars($text);
    }
    
    public static function getRolePrefix($role) : STRING
    {
        return $role;
    }
    
    public static function parseColorVars(&$string) : STRING
    {
        $string = preg_replace_callback(
            "/(\\\&|\&)[0-9a-fk-or]/",
            function (array $matches) {
                return str_replace("\\§", "&", str_replace("&", "§", $matches[0]));
            },
            $string
        );
        $replace = ["<empty>", "<black>", "<navy>", "<green>", "<teal>", "<dark_red>", "<red>",
            "<purple>", "<gold>", "<orange>", "<silver>", "<gray>", "<grey>",
            "<blue>", "<lime>", "<aqua>", "<rose>", "<pink>", "<yellow>",
            "<white>", "<magic>", "<bold>", "<strong>", "<strike>", "<strikethrough>",
            "<under>", "<underline>", "<italic>", "<em>", "<reset>", "<l>",
            "<logo>", "<a>", "<art>", "<n>", "<notice>", "<i>", "<info>",
            "<g>", "<good>", "<b>", "<bad>", "<k>", "<key>", "<v>",
            "<value>", "<h>", "<highlight>", "<c>", "<command>", "<p>", "<param>", 
        ];
        $with = ["", "§0", "§1", "§2", "§3", "§4", "§c",
            "§5", "§6", "§6", "§7", "§8", "§8", "§9",
            "§a", "§b", "§c", "§d",
            "§e", "§f", "§k", "§l", "§l", "§m",
            "§m", "§n", "§n", "§o", "§o", "§r",
            "§2", "§2", "§6", "§6", "§7", "§7",
            "§e", "§e", "§a", "§a", "§c", "§c",
            "§b", "§b", "§d", "§d", "§d", "§d",
            "§b", "§b", "§3", "§3"
        ];
        $string = str_replace($replace, $with, $string);
        while(strpos($string, "<random>") !== false) {
            self::str_replace_first("<random>", self::randomColor(), $string);
        }
        while(($p = strpos($string, "<rainbow>")) !== false) {
            $c = self::rainbow(substr($string, $p));
            for($i = 0; $p <= strlen($c); $p++, $i++) {
                $string{$i} = $c{$i};
            }
        }
        return $string;
    }
    
    public static function str_replace_first(string $from, string $to, string &$subject)
    {
        $from = '/'.preg_quote($from, '/').'/';
        $subject = preg_replace($from, $to, $subject, 1);
    }
    
    public static function getRelationColor($rel){
        switch(strtolower($rel)) {
            case "neutral":
                return TextFormat::GREEN;
            case "truce":
                return TextFormat::GOLD;
            case "enemy":
                return TextFormat::RED;
            case "ally":
                return TextFormat::DARK_GREEN;
        }
        return "";
    }
    
    public static function titleize($string){
        return TextFormat::GOLD . str_repeat("_", 7) . ".[ " . TextFormat::WHITE . $string . TextFormat::GOLD . " ]." . str_repeat("_", 7);
    }
    
    public static function aan(string $string){
        if(self::strpos(substr($string, 0, 1), ["a", "e", "o", "i", "u"], 0) === 0) {
            return "an";
        }
        return "a";
    }
    
    public static function getNicedEnum(string $string) {
        # todo
        return $string;
    }
    
    public static function strpos(string $haystack, $needle, $offset = 0) {
        if(!is_array($needle)) $needle = [$needle];
        foreach($needle as $n) {
            if(($p = strpos($haystack, $needle, $offset)) !== false) return $n;
        }
        return false;
    }
    
    public static function randomColor() {
        $colors = [ "§0", "§1", "§2", "§3", "§4", "§5", "§6", "§7", "§8", "§9", "§a", "§b", "§c", "§d", "§e", "§f"];
        return $colors[array_rand($colors)];
    }
    
    public static function rainbow(string $text, bool $reverse = false, $repeat = true, $repeatReverse = true, $offset = 0) {
        $colors = ["§4", "§c", "§6", "§e", "§2", "§a", "§b", "§3", "§1", "§9", "§d", "§5", "§f", "§7", "§8"];
        if($reverse) $colors = array_reverse($colors);
        $p = str_split($text, 1);
        $r = "";
        $i = $offset;
        if(!isset($colors[$i])) throw new \InvalidArgumentException("invalid offset ($offset) given");
        foreach($p as $c => $l) {
            $r .= $colors[$i].$l;
            $i++;
            if($i >= count($colors)) $i = 0;
            if(!$i) {
                if(!$repeat) {
                    $r .= substr($text, $c);
                    break;
                }
                if($repeatReverse){ $colors = array_reverse($colors); $i++; }
            }
        }
        return $r;
    }
    
}