<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.16.4
 * Time: 14:50
 */

namespace fpe\economizer\transistor;


use fpe\economizer\Transistor;
use pocketmine\Player;
use economy\iEconomy as IE;

class iEconomy extends Transistor
{

    public function ready(): bool
    {
        return $this->getAPI() instanceof IE && $this->getAPI()->isEnabled();
    }

    /**
     * @param Player|string $player
     * @return int|float
     */
    public function balance($player)
    {
        return $this->getAPI()->getAccount($player)->getMoney();
    }

    /**
     * @param Player|string $player
     * @param int|float $money
     * @param array $params = [] in case of other settings
     */
    public function setMoney($player, $money, array $params = [])
    {
        $this->getAPI()->getAccount($player)->setMoney($money);
    }

    /**
     * @param Player|string $player
     * @param int|float $money
     * @param array $params = [] in case of other settings
     */
    public function addMoney($player, $money, array $params = [])
    {
        $this->getAPI()->getAccount($player)->addMoney($money);
    }

    /**
     * @param Player|string
     * @param int|float $money
     * @param array $params = [] in case of other settings
     */
    public function takeMoney($player, $money, array $params = [])
    {
        $this->getAPI()->getAccount($player)->takeMoney($money);
    }

    public function getMoneyUnit() : string
    {
        return $this->getAPI()->getConfig()->get("currency-symbol", "$");
    }

}