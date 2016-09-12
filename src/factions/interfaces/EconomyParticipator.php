<?php

namespace factions\interfaces;

interface EconomyParticipator
{

    public function getMoney();

    public function takeMoney(int $amount, bool $force = false);

    public function addMoney(int $amount);

    public function setMoney(int $amount);

}