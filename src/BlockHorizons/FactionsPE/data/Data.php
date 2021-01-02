<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\data;

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
        $this->hash = md5(json_encode($this->__toArray()));
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

    // // AUTO SAVE FUNCTION
    // // When someone calls set, toggle, reset
    // // This class data will be saved
    // public function __call($name, $arguments)
    // {
    //     if(Text::strpos($name, ["set", "toggle", "reset"])) {
    //         if($this->isDataChanged()) {
    //             $this->changed();
    //         }
    //     }
    // }

}