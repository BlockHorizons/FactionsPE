<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 17.24.3
 * Time: 19:01
 */

namespace BlockHorizons\FactionsPE\task;

use mysqli;
use pocketmine\scheduler\Task;

class MySQLPinger extends Task
{

    /** @var  mysqli */
    private $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    public function onRun(int $currentTick)
    {
        @$this->connection->ping();
    }

}