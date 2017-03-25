<?php

/** @var $this \factions\FactionsPE */
/** @var \factions\data\provider\MySQLDataProvider $dp */
$dp = $this->getDataProvider();
var_dump($dp->gen_ins_query(["name" => "ChrisPrime", "age" => 18], 'users', true));