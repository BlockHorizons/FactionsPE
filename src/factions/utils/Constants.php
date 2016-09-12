<?php
namespace factions\utils;

use pocketmine\utils\TextFormat;

class Constants
{
    // ASCII Map
    CONST MAP_WIDTH = 48;
    CONST MAP_HEIGHT = 8;
    CONST MAP_HEIGHT_FULL = 17;

    CONST MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
    CONST MAP_KEY_WILDERNESS = TextFormat::GRAY . "-";
    CONST MAP_KEY_SEPARATOR = TextFormat::AQUA . "+";
    CONST MAP_KEY_OVERFLOW = TextFormat::WHITE . "-" . TextFormat::WHITE; # ::MAGIC?
    CONST MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Too Many Factions (>" . 107 . ") on this Map.";

}