<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 8/17/16
 * Time: 2:32 PM
 */

namespace factions\utils;


use pocketmine\command\CommandSender;

class Pager
{

    protected $max = 0;

    /**
     * @var CommandSender
     */
    protected $sender;

    public function __construct(string $header, int $page, int $height, array $objects, \Closure $stringifier, CommandSender $sender) {
        $this->header = $header;
        $this->page = $page;
        $this->height = $height;
        $this->objects = $objects;
        $this->stringifier = $stringifier;
        $this->sender = $sender;
    }

    /** @var int */
    protected $page = 1;
    protected $height = 5;

    protected $header = "";

    /**
     * @var \Closure
     */
    protected $stringifier;
    /**
     * @var mixed[]
     */
    protected $objects = [];

    /**
     * @var String[]
     */
    public $output = [];

    public function stringify() {
        $objects = array_chunk($this->objects, $this->height);
        $this->max = count($objects);
        $page = (int)min(count($objects), $this->page);
        if ($page < 1) {
            $page = 1;
        }
        $this->page = $page;
        $res = [];
        foreach ($objects[$page-1] as $i => $o) {
            $res[] = $this->stringifier($o, $i);
        }
        $this->output = $res;
    }

    public function stringifier($object, int $index) {
        $closure = $this->stringifier;
        return $closure($object, $index, $this->sender);
    }

    public function getOutput() : array {
        return $this->output;
    }

    public function getHeader() { return $this->header; }

    public function getPage()
    {
        return $this->page;
    }

    public function getMax() {
        return $this->max;
    }

    public function getSender() : CommandSender {
        return $this->sender;
    }

    public function setObjects(array $objects)
    {
        $this->objects = $objects;
    }

    public function setHeader(string $header) {
        $this->header = $header;
    }

}