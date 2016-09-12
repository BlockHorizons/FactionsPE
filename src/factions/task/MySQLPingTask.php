<?php
namespace factions\task;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

/**
 * This task will ping database to avoid timeout
 * Schedule it to 600 ticks (seconds: 600 / 20 = 30)
 *
 * @author Primus
 */
class MySQLPingTask extends PluginTask
{

    /** @var \mysqli $database */
    private $database;

    public function __construct(Plugin $owner, \mysqli $database)
    {
        parent::__construct($owner);
        $this->database = $database;
    }

    public function onRun($currentTick)
    {
        $this->database->ping();
    }

}