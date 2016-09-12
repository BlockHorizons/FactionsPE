<?php
namespace factions\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class QuickAsyncTask extends AsyncTask {
    
    /**
     * @var \Closure
     */
    private $action;
    
    /**
     * @var \Callback
     */
    private $callback;

    public function __construct(\Closure $action, callable $callback) {
        $this->action = $action;
        $this->callback = $callback;
    }
    
    public function onRun() {
        $action = $this->action;
        $this->setResult($action());
    }
    
    
    public function onCompletion(Server $server) {
        call_user_func_array($this->callback, [$this->getResult()]);
    }
}