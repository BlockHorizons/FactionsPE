<?php
namespace factions\utils;

use localizer\Localizer;
use localizer\Translatable;
use pocketmine\command\CommandSender;

class Pager {

	/** @var string[] */
	public $output = [];
	protected $max = 0;
	/** @var CommandSender|null */
	protected $sender;
	/** @var int */
	protected $page = 1, $height = 5;
	/** @var string */
	protected $title = "";
	/** @var closure|null */
	protected $stringifier;
	/** @var array */
	protected $objects = [];

	/**
	 * @param Translatable|string $title
	 * @param int $page
	 * @param int $height
	 * @param mixed[] $objects
	 * @param CommandSender|null $sender
	 * @param Closure|null $stringifier
	 */
	public function __construct($title, int $page, int $height, array $objects, CommandSender $sender = null, \Closure $stringifier = null) {
		$this->title       = $title;
		$this->page        = $page;
		$this->height      = $height;
		$this->objects     = $objects;
		$this->stringifier = $stringifier;
		$this->sender      = $sender;
	}

	public function getOutput(): array
	{
		return $this->output;
	}

	public function getTitle() {
		return $this->title;
	}

	public function sendTitle(CommandSender $sender, array $params = [], bool $titleize = false) {
		if ($this->title !== null) {
			$params = array_merge([
				"page" => $this->getPage(),
				"max"  => $this->getMax(),
			], $params);
			if ($this->title instanceof Translatable) {
				$this->title->addParams($params);
				$sender->sendMessage($titleize ? Text::titleize($this->title) : $this->title);
			} else {
				$t = Localizer::trans($this->title, $params);
				$sender->sendMessage($titleize ? Text::titleize($t) : $t);
			}
		}
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getPage(): int {
		return $this->page;
	}

	public function getMax(): int {
		return $this->max;
	}

	/**
	 * @return CommandSender|null
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * @return mixed[]
	 */
	public function setObjects(array $objects) {
		$this->objects = $objects;
	}

	public function __toString() {
		if (empty($this->output)) {
			$this->stringify();
		}
		return implode(PHP_EOL, $this->output);
	}

	public function stringify() {
		$objects   = array_chunk($this->objects, $this->height);
		$this->max = count($objects);
		$page      = (int) min(count($objects), $this->page);
		if ($page < 1) {
			$page = 1;
		}
		$this->page = $page;
		if (empty($objects)) {
			return;
		}

		$res  = [];
		$objs = $objects[$page - 1];
		do {
			foreach ($objs as $i => $o) {
				if ($this->height <= count($this->output)) {
					break;
				}

				$r = $this->stringifier($o, $i);
				if ($r === null) {
					continue;
				}

				$res[] = $r;
			}
			$page++;
		} while ($this->height <= count($this->output) && isset($objects[$page]) && $objs = $objects[$page - 1]);
		$this->output = $res;
	}

	public function stringifier($object, int $index) {
		$closure = $this->stringifier;
		return $closure($object, $index, $this->sender);
	}

	public function __toArray() {
		return $this->objects;
	}

}
