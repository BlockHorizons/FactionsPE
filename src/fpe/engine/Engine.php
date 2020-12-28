<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\engine;

use fpe\FactionsPE;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginLogger;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;

/**
 * Engines are listeners which control behaviour of the plugin by communicating
 * with back-end
 */
abstract class Engine extends Task implements Listener
{

    /** @var FactionsPE */
    protected $main;
    /** @var TaskHandler */
    protected $task;

    public function __construct(FactionsPE $main, int $loop = -1)
    {
        $this->main = $main;

        $this->setup();

        if ($loop > 0) {
            $this->startLoop($loop);
        }
    }

    /**
     * Do whatever you need
     */
    public function setup()
    {
        # Do nothing
    }

    /**
     * Starts a loop. If no params given default interval, one second, is used.
     * @param int $interval
     * @throws \Exception
     */
    public function startLoop(int $interval = 20)
    {
        if ($this->task !== null && !$this->task->isCancelled()) {
            throw new \Exception("Loop already running");
        }
        $this->task = $this->getMain()->getScheduler()->scheduleRepeatingTask($this, $interval);
    }

    public function getMain(): FactionsPE
    {
        return $this->main;
    }

    public function onRun(int $currentTick)
    {
        # Do nothing
    }

    public function stopLoop()
    {
        if (!$this->isLooping()) {
            throw new \Exception("Loop is not running");
        }
        $this->getMain()->getScheduler()->cancelTask($this->getTaskId());
        $this->task = null;
    }

    public function isLooping(): bool
    {
        return $this->task !== null && !$this->task->isCancelled();
    }

    public function getLogger(): PluginLogger
    {
        return $this->getMain()->getLogger();
    }

}
