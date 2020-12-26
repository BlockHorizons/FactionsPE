<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */
namespace fpe\utils;

final class Gameplay
{

    /**
     * @var mixed[]
     */
    private static $data = [];

    private function __construct()
    {
    }

    // ---------------------------------------------------------------------------
    // FUNCTIONS
    // ---------------------------------------------------------------------------

    public static function getData(): array
    {
        return self::$data;
    }

    public static function setData(array $data)
    {
        self::$data = $data;
    }

    public static function set(string $key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        $data = self::$data;
        if (strpos($key, ".") !== false) {
            $keys = explode(".", $key);
            $i = 0;
            while (isset($data[$keys[$i]])) {
                $data = $data[$keys[$i]];
                if (!is_array($data)) return $data;
                $i++;
                if (!isset($keys[$i])) return $data;
            }
        }
        if (isset($data[$key])) return $data[$key];
        return $default;
    }

}
