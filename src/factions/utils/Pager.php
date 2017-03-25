<?php
namespace factions\utils;

use pocketmine\command\CommandSender;

class Pager
{

    /** @var string[] */
    public $output = [];
    protected $max = 0;
    /** @var CommandSender|null */
    protected $sender;
    /** @var int */
    protected $page = 1, $height = 5;
    /** @var string */
    protected $header = "";
    /** @var closure|null */
    protected $stringifier;
    /** @var array */
    protected $objects = [];

    /**
     * @param string $header
     * @param int $page
     * @param int $height
     * @param mixed[] $objects
     * @param CommandSender|null $sender
     * @param Closure|null $stringifier
     */
    public function __construct(string $header, int $page, int $height, array $objects, CommandSender $sender = null, \Closure $stringifier = null)
    {
        $this->header = $header;
        $this->page = $page;
        $this->height = $height;
        $this->objects = $objects;
        $this->stringifier = $stringifier;
        $this->sender = $sender;
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header)
    {
        $this->header = $header;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @return CommandSender|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return mixed[]
     */
    public function setObjects(array $objects)
    {
        $this->objects = $objects;
    }

    public function __toString()
    {
        if (empty($this->output)) {
            $this->stringify();
        }
        return implode(PHP_EOL, $this->output);
    }

    public function stringify()
    {
        $objects = array_chunk($this->objects, $this->height);
        $this->max = count($objects);
        $page = (int)min(count($objects), $this->page);
        if ($page < 1) {
            $page = 1;
        }
        $this->page = $page;
        $res = [];
        foreach ($objects[$page - 1] as $i => $o) {
            $r = $this->stringifier($o, $i);
            if ($r === null) continue;
            $res[] = $r;
        }
        $this->output = $res;
    }

    public function stringifier($object, int $index)
    {
        $closure = $this->stringifier;
        return $closure($object, $index, $this->sender);
    }

    public function __toArray()
    {
        return $this->objects;
    }

}
