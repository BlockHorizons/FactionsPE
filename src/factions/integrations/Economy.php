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

namespace factions\integrations;


use factions\FactionsPE;
use localizer\Localizer;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Economy
{

    private static $instance = null;

    /** @var array $prices */
    protected $prices = [];

    /** @var mixed $economy */
    protected $economy;

    /**
     * Economy constructor.
     * @param FactionsPE $main
     * @param string $preferred
     */
    public function __construct(FactionsPE $main, $preferred = "EconomyAPI")
    {
        if (self::$instance instanceof Economy) throw new \InvalidStateException("class is using singleton structure");

        self::$instance = $this;
        $this->server = $main->getServer();
        $this->prices = $main->getConfig()->get('prices', []);
        
        $economy = ["EconomyAPI", "PocketMoney", "MassiveEconomy", "GoldStd"];
        $ec = [];
        $e="none";
        foreach($economy as $ep){
            $ins = $this->server->getPluginManager()->getPlugin($ep);
            if($ins instanceof Plugin && $ins->isEnabled()){
                $ec[$ins->getName()] = $ins;
            }
            $e=$ep;
        }
        if (isset($ec[$preferred])) {
            $this->economy = $ec[$preferred];
        } else {
            if(!empty($ec)){
                $this->economy = $ec[array_rand($e)];
            }
        }
        if($this->isLoaded()){
            $main->getLogger()->info(Localizer::trans('plugin.economy-set', $this->getName()));
        } else {
            $main->getLogger()->info(Localizer::trans('plugin.economy-failed'));
            $main->getLogger()->info(Localizer::trans('plugin.economy-dummy'));
            $this->economy = new DummyEconomy();
        }
    }

    public function isLoaded() : bool
    {
        if ($this->economy instanceof Plugin and $this->economy->isEnabled()) return true; else return false;
    }

    public function getName() : string
    {
        if ($this->economy instanceof DummyEconomy) return $this->economy->getName();
        if ($this->economy instanceof Plugin) return $this->economy->getDescription()->getName();
        return "NONE";
    }

    /** Get instance of constructed Economy class */
    public static function get() : Economy { return self::$instance; }

    /**
     * Returns beautifully formatted money
     *
     * @param $amount
     * @return string
     */
    public function formatMoney($amount) : string
    {
        if($this->getName() === 'EconomyAPI') return $this->getMonetaryUnit() . $amount;
        if($this->getName() === 'PocketMoney') return $amount . ' ' . $this->getMonetaryUnit();
        if($this->getName() === 'GoldStd') return $amount . $this->getMonetaryUnit();
        if($this->getName() === 'MassiveEconomy') return $this->getMonetaryUnit() . $amount;
        return $amount;
    }

    public function getMonetaryUnit() : string
    {
        if($this->getName() === 'EconomyAPI') return $this->economy->getMonetaryUnit();
        if($this->getName() === 'PocketMoney') return 'PM';
        if($this->getName() === 'GoldStd') return 'G';
        if($this->getName() === 'MassiveEconomy') return $this->economy->getMoneySymbol() != null ? $this->economy->getMoneySymbol() : '$';
        return "";
    }

    public function takeMoney(Player $player, $amount, $force=false){
        if ($this->getName() === "Dummy") return true;
        if($this->getName() === "EconomyAPI") return $this->economy->reduceMoney($player, $amount, $force);
        if($this->getName() === "PocketMoney") return $this->economy->reduceMoney($player, $amount, $force);
        if($this->getName() === "GoldStd") return $this->economy->reduceMoney($player, $amount, $force);
        if($this->getName() === "MassiveEconomy") return $this->economy->takeMoney($player, $amount, $force);
        return false;
    }

    public function giveMoney(Player $player, $amount)
    {
        if ($this->getName() === "Dummy") return true;
        if ($this->getName() === "EconomyAPI") return $this->economy->setMoney($this->getMoney($player) + $amount);
        # TOOD
        return false;
    }

    public function getMoney(Player $player) : int
    {
        if ($this->getName() === 'EconomyAPI') return $this->economy->myMoney($player);
        if ($this->getName() === 'PocketMoney') return $this->economy->getMoney($player->getName());
        if ($this->getName() === 'GoldStd') return $this->economy->getMoney($player); // Check
        if ($this->getName() === 'MassiveEconomy') {
            if ($this->economy->isPlayerRegistered($player->getName())) {
                return $this->economy->getMoney($player->getName());
            }
        }
        if ($this->getName() === "Dummy") return PHP_INT_MAX;
        return 0;
    }

    public function getAPI() { return $this->economy; }

    public function getPrice($node) : int {
        $dirs = explode(".", $node);
        $i = 0;
        $op = $this->prices;
        while(isset($dirs[$i]) and isset($op[$dirs[$i]])){
            if(!is_array($op[$dirs[$i]])) return (int) $op[$dirs[$i]];
            $op = $op[$dirs[$i]];
            $i++;
        }
        return 0;
    }

    public function addMoney($player, $reward)
    {
    }

}