<?php
/*
 *   Dominate: Advanced command library for PocketMine-MP
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
namespace dominate;

use dominate\parameter\Parameter;
use dominate\requirement\Requirement;
use fpe\FactionsPE;
use localizer\Localizer;
use Message;
use pocketmine\command\Command as PocketMineCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

class Command extends PocketMineCommand implements PluginIdentifiableCommand {

	/**
	 * @var Command
	 */
	protected $parent;

	/**
	 * @var Requirement[]
	 */
	protected $requirements = [];

	/**
	 * @var Command[]
	 */
	protected $childs = [];

	/**
	 * @var Parameter[]
	 */
	protected $parameters = [];

	/**
	 * Values from each parameter
	 * @var mixed[]
	 */
	protected $values = [];

	/**
	 * @var Command
	 */
	protected $endPoint = null;

	/**
	 * Send error messagem if arguments > parameter count
	 * @var bool
	 */
	protected $overflowSensitive = true;

	/**
	 * Swap arguments if given in wrong order
	 * @var bool
	 */
	protected $swap = true;

	/**
	 * Give sender suggestions if more than command met token
	 * @var bool
	 */
	protected $smart = true;

	// Last execution parameters

	/** @var CommandSender|null */
	protected $sender;

	/** @var string[] */
	protected $args = [];

	/** @var string */
	protected $label;

	/** @var Plugin */
	protected $plugin;

    /**
     * @var string
     */
    private $usage;

    /**
	 * @param Plugin|Command $owner
	 * @param string $name
	 * @param string $description = ""
	 * @param string $permission
	 * @param string[] $aliases = []
	 * @param Parameter[] $parameters = []
	 * @param Command[] $childs = []
	 */
	public function __construct($owner, string $name, string $description = "", string $permission, array $aliases = [], array $parameters = [], array $childs = []) {
		parent::__construct($name, $description, "", $aliases);
		$this->setPermission($permission);
		$this->plugin = $owner instanceof Command ? $owner->getPlugin() : $owner;
		if ($owner instanceof Command) {
			$this->setParent($owner);
		}
		$this->parameters = $parameters;
		$this->setChilds($childs);

		$this->setup();
		$this->setUsage($this->getUsage());
	}

	/**
	 * Whenever parent or childs have changed
	 */
	public function chainUpdate() {

	}

	/**
	 * Add requirements, permissions and parameters here
	 */
	public function setup() {}

	/*
		 * ----------------------------------------------------------
		 * CHILD (Sub Command)
		 * ----------------------------------------------------------
	*/

	/**
	 * @return Command|null
	 */
	public function getRoot() {
		return $this->getChain()[0];
	}

	/**
	 * Super command being the first element of array
	 * @return Command[]
	 */
	public function getChain(): array{
		$chain = [$this];
		if (!$this->isChild()) {
			return $chain;
		}

		$parent  = $this->parent;
		$chain[] = $parent;
		while ($parent->isChild()) {
			$parent  = $parent->getParent();
			$chain[] = $parent;
		}
		return array_reverse($chain);
	}

	/**
	 * @return Command[]
	 */
	public function getChildsByToken(string $token): array
	{
		if (strlen($token) < 1) {
			return [];
		}

		$matches = [];
		foreach ($this->childs as $child) {
			if ($token === ($name = $child->getName())) {
				$matches[] = $child;
				break;
			}
			$hay = [0 => $name];
			$hay = array_merge($child->getAliases(), $hay);
			foreach ($hay as $al) {
				if (($p = strpos($al, $token)) === 0) {
					$matches[$al] = $child;
					break;
				}
			}
		}
		ksort($matches);
		return array_values($matches);
	}

	/**
	 * Get single command object by name
	 */
	public function getChild(string $name) {
		return $this->getChildsByToken($name)[0] ?? null;
	}

	/**
	 * Registers new subcommand or replaces existing one
	 *
	 * @param Command $command
	 * @param int $index if null then the child will be added at the end of array
	 */
	public function addChild(Command $command, int $index = null) {
		if ($this->contains($command)) {
			throw new \InvalidArgumentException("command '{$command->getName()}' is already a child of '{$this->getName()}'");
		}

		if ($this->getParent() === $command) {
			throw new \LogicException("parent can not be child");
		}

		if ($command->contains($this)) {
			throw new \LogicException("parent '{$command->getName()}' can't be child of child");
		}

		$this->childs[($index ?? count($this->childs))] = $command;
		$command->setParent($this);
		$this->chainUpdate();
	}

	/**
	 * @var Command[]
	 */
	public function addChilds(array $childs) {
		foreach ($childs as $child) {
			$this->addChild($child);
		}
	}

	/**
	 * @var Command[]
	 */
	public function setChilds(array $childs) {
		foreach ($this->childs as $child) {
			$this->removeChild($child);
		}
		foreach ($childs as $child) {
			$this->addChild($child);
		}
	}

	public function contains(Command $command): bool {
		return in_array($command, $this->childs, true);
	}

	public function removeChild(Command $command) {
		if ($this->contains($command)) {
			unset($this->childs[array_search($command, $this->childs, true)]);
			$command->setParent(null);
		}
	}

	public function getChilds(): array{
		return $this->childs;
	}

	public function isChild(): bool {
		return $this->parent instanceof Command;
	}

	public function isParent(): bool {
		return !empty($this->childs);
	}

	public function getParent() {
		return $this->parent;
	}

	public function setParent(Command $command) {
		if ($this === $command) {
			throw new \LogicException("command can not be parent of self");
		}

		// TODO: other logic checks
		$this->parent = $command;
		$this->chainUpdate();
	}

	/*
		 * ----------------------------------------------------------
		 * PARAMETER
		 * ----------------------------------------------------------
	*/

	public function addParameter(Parameter $arg) {
		$this->parameters[] = $arg;
		$arg->setIndex($this->getParameterIndex($arg));
	}

	public function removeParameter(Parameter $arg) {
		if (($i = $this->getParameterIndex($arg)) >= 0) {
			unset($this->parameters[$i]);
		}
	}

	public function getParameterIndex(Parameter $arg): int {
		foreach ($this->parameters as $i => $a) {
			if ($a === $arg) {
				return $i;
			}

		}
		return -1;
	}

	/**
	 * @param int $index
	 * @return Parameter|null
	 */
	public function getParameterAt(int $index) {
		return $this->parameters[$index] ?? null;
	}

	public function getParameter(string $name) {
		foreach ($this->parameters as $param) {
			if ($param->getName() === $name) {
				return $param;
			}

		}
		return null;
	}

	public function getArgument(int $index, $default = null) {
		if (isset($this->parameters[$index])) {
			return $this->parameters[$index]->getValue($default);
		}
		return null;
	}

	public function isArgumentSet(int $index) {
		return isset($this->values[$index]);
	}

	public function getRequiredParameterCount(): int{
		$i = 0;
		foreach ($this->parameters as $a) {
			if ($a->isRequired($this->sender)) {
				$i++;
			}

		}
		return $i;
	}

	/*
		 * ----------------------------------------------------------
		 * REQUIREMENTS
		 * ----------------------------------------------------------
	*/

	public function hasRequirement(Requirement $r) {
		return in_array($r, $this->requirements, true);
	}

	public function addRequirement(Requirement $r) {
		$this->requirements[] = $r;
	}

	public function testRequirements(CommandSender $sender = null,  ? bool $silent = false) : bool{
		$sender = $sender ?? $this->sender;
		foreach ($this->requirements as $requirement) {
			if (!$requirement->hasMet($sender, $silent)) {
				return false;
			}

		}
		return true;
	}

	public function getUsage(): string{
		$sender = $this->sender;
		$usage  = "/";
		// add chain
		$chain = $this->getChain();
		foreach ($chain as $cmd) {
			$usage .= $cmd->getName() . " ";
		}
		foreach ($this->parameters as $param) {
			$usage .= $param->getTemplate($sender) . " ";
		}
		if (empty($this->parameters) && !empty($this->getChilds())) {
			$usage .= "<command> ";
		}
		$usage       = trim($usage);
		$this->usage = $usage;
		return $usage;
	}

	public function sendUsage(CommandSender $sender = null) {
		$sender = $sender ?? $this->sender;
		if (!($sender instanceof CommandSender)) {
			return;
		}

		if (class_exists("\\localizer\\Localizer")) {
			if (($msg = Localizer::trans("command-usage", ["usage" => $this->getUsage()])) !== "command-usage") {
				$sender->sendMessage($msg);
				return;
			}
		}
		$sender->sendMessage($this->getUsage());
	}

	/*
     * ----------------------------------------------------------
     * EXECUTION
     * ----------------------------------------------------------
	*/

    /**
     * @param CommandSender $sender
     * @param string $label
     * @param array $args
     * @return bool
     */
	public function prepare(CommandSender $sender, string $label, array $args): bool {
		return true;
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		$this->sender = $sender;
		$this->label  = $label;
		$this->args   = $args;

		if (!$this->prepare($sender, $label, $args)) {
			return false;
		}
		if (!$this->testPermission($sender)) {
			return false;
		}
		if (!$this->testRequirements()) {
			return false;
		}
		if (($argCount = count($args)) < $this->getRequiredParameterCount() && $this->overflowSensitive) {
			$this->sendUsage($sender);
			return false;
		}

		if ($this->swap) {
			$this->swap();
		}
		foreach ($this->parameters as $i => $param) {

			$args = $this->args;
			if (!isset($args[$i]) and !$param->isDefaultValueSet()) {
				break;
			}

			$value = isset($args[$i]) ? $args[$i] : $param->getDefaultValue();
			if ($param->getType() !== Parameter::TYPE_NULL && $value !== null) {
				if ($param->isPermissionSet() && $this->isArgumentSet($i)) {
					if (!$param->testPermission($sender)) {
						$sender->sendMessage(Localizer::translatable("parameter.permission-denied", [
							"param" => $param->getName(),
						]));
						return false;
					}
				}
				$param->setValue($param->read($value, $sender));
				# If more than one param was validated at once, then which error message should we use? # TODO
				if (!$param->isValid($param->getValue(), $sender) && !$param->getNextParameter()) {
					$sender->sendMessage($param->createErrorMessage($sender, $value));
					return false;
				} elseif ($param->getNextParameter()) {
					while ($param = $param->getNextParameter()) {
						$param->setValue($param->read($value, $sender));
						if (!$param->isValid($param->getValue(), $sender) && !$param->getNextParameter()) {
							$sender->sendMessage($param->createErrorMessage($sender, $value));
						}
					}
				}
			}
			$this->values[$i] = $value;
			$this->args       = $args;
		}

		if (!empty($this->childs) and count($this->values) > 0) {
			if ($this->values[0] === "") {
				$this->sendUsage($sender);
				return false;
			}
			$matches = $this->getChildsByToken((string) $this->values[0]);

			if (($matchCount = count($matches)) === 1) {
				array_shift($args);
				$r              = $matches[0]->execute($sender, $label, $args);
				$this->endPoint = $matches[0]->endPoint;
				return $r;
			} else {
				$this->endPoint = $this;
				if (!$this->smart) {
					return false;
				}

				// Token was too ambiguous
				if ($matchCount > 8) {
					$sender->sendMessage(Localizer::trans("command-too-ambiguous", ["token" => $this->values[0]]));
					return false;
				}
				// No commands by token was found
				if ($matchCount === 0) {
					$sender->sendMessage(Localizer::trans("command-child-none", ["token" => $this->values[0]]));
					return false;
				}
				// Few commands by token was found an suggestion table will be created
				$sender->sendMessage(Localizer::trans("command-suggestion-header", ["token" => $this->values[0]]));
				foreach ($matches as $match) {
					$sender->sendMessage(Localizer::trans("command-suggestion", ["match" => $match->getName(), "usage" => $match->getUsage($sender), "desc" => $match->getDescription()]));
				}
				return false;
			}
		}

		try {
			$return = $this->perform($sender, $label, $args);
			if (is_string($return)) {
				$sender->sendMessage(Localizer::translatable($return));
			} elseif (is_array($return)) {
				$params = [];
				switch (count($return)) {
				case 0:
					break;
				case 1:
					$msg    = $return[0];
					$params = [];
					break;
				default:
					$msg    = $return[0];
					$params = array_pop($return);
					if (!is_array($params)) {
						$params = [$params];
					}

				}
				if (isset($msg)) {
					$sender->sendMessage(Localizer::translatable($msg, $params));
				}
			}
		} catch (ThrowableMessage $e) {
			$sender->sendMessage(Localizer::translatable($e->getMessage(), []));
		}
		$this->reset();
		return true;
	}

	public function perform(CommandSender $sender, $label, array $args) {
		return true;
	}

	// SWAP

	private function swap() {
		// TODO
	}

	public function swappingEnabled(): bool {
		return $this->swap;
	}

	public function setSwapping(bool $value) {
		$this->swap = $value;
	}

	public function toggleSwapping() {
		$this->swap = !$this->swap;
	}

	// SMART

	public function isSmart(): bool {
		return $this->smart;
	}

	public function setSmart(bool $value) {
		$this->smart = $value;
	}

	public function toggleSmart() {
		$this->smart = !$this->smart;
	}

	public function reset() {
		$this->sender = null;
		$this->label  = "";
		$this->args   = [];
		$this->values = [];
		foreach ($this->parameters as $param) {
			$param->unset();
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin {
		return $this->plugin;
	}

}
