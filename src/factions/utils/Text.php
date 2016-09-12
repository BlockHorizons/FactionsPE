<?php
/*
 *   88""Yb     88""Yb     88     8b    d8     88   88     .dP"Y8
 *   88__dP     88__dP     88     88b  d88     88   88     `Ybo."
 *   88"""      88"Yb      88     88YbdP88     Y8   8P     o.`Y8b
 *   88         88  Yb     88     88 YY 88     `YbodP'     8bodP'
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Latvian PHP programmer Kristaps Drivnieks (Primus)
 * @link https://Github.com/PrimusLV/FactionsPE
 */

namespace factions\utils;


use factions\FactionsPE;
use pocketmine\utils\TextFormat;

class Text
{

    const FALLBACK_LANGUAGE = "eng";
    /** @var string $langFolder */
    protected static $langFolder;
    /** @var Text $instance */
    private static $instance;
    /** @var string $PREFIX */
    private static $PREFIX;
    /** @var string[] $bannedWords */
    private static $bannedWords;
    private static $text = "";
    private static $params = [];
    private static $lang = [];
    private static $fallbackLang = [];
    /** @var bool $constructed */
    private static $constructed = false;
    private static $formats = [];
    /** @var FactionsPE $plugin */
    protected $plugin;

    public function __construct(FactionsPE $plugin, $lang = "eng")
    {
        if (self::$constructed) throw new \RuntimeException("class is in singleton structure");
        self::$instance = $this;
        $this->plugin = $plugin;
        self::$langFolder = $plugin->getDataFolder()."languages/";
        self::$PREFIX = "&7[&c" . $plugin->getDescription()->getName() . "&7]&r&f";
        self::$formats = $plugin->getConfig()->get('formats', [
            "nametag" => "[{RANK}{FACTION}] {PLAYER}",
            "chat" => [
                "normal" => "[{RANK}{FACTION}] {PLAYER}: {MESSAGE}",
                "faction" => "&7F:&f [{RANK}{FACTION}] {PLAYER}: {MESSAGE}"
            ],
            "rank" => [
                "leader" => "***",
                "officer" => "**",
                "member" => "*",
                "recruit" => "^"
            ],
            "hud" => [
                "text" => "*** HUD NOT SET ***",
                "sub-title" => ""
            ]
        ]);
        self::$bannedWords = $plugin->getConfig()->get('banned-words', []);

        $this->loadLang(self::$langFolder.$lang.'.ini', self::$lang);
        $this->loadLang(self::$langFolder.self::FALLBACK_LANGUAGE.'.ini', self::$fallbackLang);

        if(!empty(self::$lang)){
            $plugin->getLogger()->info(self::parse('plugin.log.language.set', $lang));
        } else {
            $plugin->getLogger()->info(self::parse('plugin.log.language.using.fallback', $lang, self::FALLBACK_LANGUAGE));
        }
        self::$constructed = true;
    }

    private function loadLang($path, &$d){
        if(file_exists($path) and strlen($content = file_get_contents($path)) > 0){
            foreach(explode("\n", $content) as $line){
                $line = trim($line);
                if($line === "" or $line{0} === "#"){
                    continue;
                }
                $t = explode("=", $line, 2);
                if(count($t) < 2){
                    continue;
                }
                $key = trim($t[0]);
                $value = trim($t[1]);
                if($value === ""){
                    continue;
                }
                $d[$key] = $value;
            }
        }
    }

    public static function parse($node, ...$vars) : string
    {
        $text = isset(self::$lang[$node]) ? self::$lang[$node] : $node;
        self::$params = $vars;
        self::$text = $text;
        return self::$instance;
    }

    public static function formatRank($rank){
        if(isset(self::$formats['rank'][$rank])){
            return self::$formats['rank'][$rank];
        }
        return "";
    }

    public static function getRolePrefix($role) : STRING
    {
        return $role;
    }

    // Formats

    public static function getFormat($format) : string {
        $dirs = explode(".", $format);
        $i = 0;
        $op = self::$formats;
        while(isset($dirs[$i]) and isset($op[$dirs[$i]])){
            if(!is_array($op[$dirs[$i]])) return self::parseColorVars($op[$dirs[$i]]);
            $op = $op[$dirs[$i]];
            $i++;
        }
        return $format;

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
        $with = [     "", "§0", "§1", "§2", "§3", "§4", "§c",
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
                $string{$c} = $c{$i};
            }
        }
        return $string;
    }

    public static function str_replace_first(string $from, string $to, string &$subject)
    {
        $from = '/'.preg_quote($from, '/').'/';

        $subject = preg_replace($from, $to, $subject, 1);
    }

    public static function isNameBanned($name) : bool {
        foreach(self::$bannedWords as $word){
            if(strpos(strtolower($name), strtolower($word)) !== false) return true;
        }
        return false;
    }

    public static function getHudText() : string
    {
        return self::$formats['hud']['text'];
    }

    public static function getHudSubTitle() : string
    {
        return self::$formats['hud']['sub-title'];
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

    public function __toString()
    {
        $s = self::$text;
        $i = 0;
        foreach (self::$params as $var) {
            $s = str_replace("%var" . $i, $var, $s);
            //$s = sprintf($s, $var);
            $i++;
        }
        $s = str_replace("%prefix", self::$PREFIX, $s);
        self::$text = "";
        self::$params = [];
        self::parseColorVars($s);
        return $s;
    }

}