<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.4.4
 * Time: 22:35
 */

$f = \factions\manager\Factions::getById(\factions\entity\Faction::WARZONE);
$f->setBank(1000);
echo "DONE".PHP_EOL;