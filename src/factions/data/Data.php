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

namespace factions\data;

use factions\utils\Text;

abstract class Data
{

    /**
     * Unix timestamp of last save (milliseconds)
     * @var integer
     */
    public $lastSaved;

    /**
     * @var string md5 hash
     */
    public $hash;

    /*
     * ----------------------------------------------------------
     * ABSTRACT
     * ----------------------------------------------------------
     */

    /**
     * Called whenever the content of class has changed
     */
    public function changed()
    {
        $this->save();
        $this->lastSaved = microtime(true);
        $this->hash = md5(json_decode($this->__toArray()));
        echo "Saved!";
        var_dump($this->__debugInfo());
    }


    /**
     * @return bool
     */
    public function isDataChanged(): bool
    {
        $hash = md5(json_encode($this->__toArray()));
        $ret = $this->hash === $hash ? true : false;
        $this->hash = $hash;
        return $ret;
    }

    /**
     * Must save this class
     */
    public abstract function save();

    /**
     * This class must return array of data ready to be saved.
     * @return array
     */
    public abstract function __toArray();

    // AUTO SAVE FUNCTION
    // When someone calls set, toggle, reset
    // This class data will be saved
    public function __call($name, $arguments)
    {
        echo $name.PHP_EOL;
        if(Text::strpos($name, ["set", "toggle", "reset"])) {
            if($this->isDataChanged()) {
                $this->changed();
            }
        }
    }

    public function __debugInfo()
    {
        return [
            "hash" => $this->hash,
            "lastSaved" => $this->lastSaved." or ".Text::ago($this->lastSaved / 1000)
        ];
    }

}