<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/29/16
 * Time: 3:58 AM
 */

namespace factions\interfaces;


interface Named
{

    public function getName() : STRING;
    public function setName(string $name);

}