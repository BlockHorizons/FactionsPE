<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2016  Chris Prime
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace factions\engine;

use factions\FactionsPE;
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

        if($loop > 0) {
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

    public function onRun(int $currentTick)
    {
        # Do nothing
    }

    /**
     * Starts a loop. If no params given default interval, one second, is used.
     */
    public function startLoop(int $interval = 20) 
    {
        if($this->task !== null && !$this->task->isCancelled()) {
            throw new \Exception("Loop already running");
        }
        $this->task = $this->getMain()->getScheduler()->scheduleRepeatingTask($this, $interval);
    }

    public function stopLoop() {
        if(!$this->isLooping()) {
            throw new \Exception("Loop is not running");
        }
        $this->getMain()->getScheduler()->cancelTask($this->getTaskId());
        $this->task = null;
    }

    public function isLooping() : bool {
        return $this->task !== null && !$this->task->isCancelled();
    }

    public function getLogger(): PluginLogger
    {
        return $this->getMain()->getLogger();
    }

    public function getMain(): FactionsPE
    {
        return $this->main;
    }

}
