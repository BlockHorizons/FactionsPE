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
namespace dominate\parameter;

use dominate\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use localizer\Translatable;
use localizer\Localizer;

class Parameter {

    const TYPE_STRING 	= 0x0;
    const TYPE_INTEGER 	= 0x1;
    const TYPE_NUMERIC 	= self::TYPE_INTEGER;
    const TYPE_FLOAT 	= 0x2;
    const TYPE_DOUBLE 	= self::TYPE_FLOAT;
    const TYPE_REAL 	= self::TYPE_FLOAT;
    const TYPE_BOOLEAN 	= 0x4;
    const TYPE_BOOL 	= self::TYPE_BOOLEAN;
    const TYPE_NULL		= 0x5;

    /** @var string[] */
    public $ERROR_MESSAGES = [
        self::TYPE_STRING 	=> "type-string",
        self::TYPE_INTEGER 	=> "type-integer",
        self::TYPE_FLOAT 	=> "type-float",
        self::TYPE_BOOLEAN 	=> "type-boolean",
        self::TYPE_NULL		=> "type-null"
    ];

    const PRIMITIVE_TYPES = [
        self::TYPE_STRING,
        self::TYPE_INTEGER,
        self::TYPE_FLOAT,
        self::TYPE_BOOLEAN,
        self::TYPE_NULL,
    ];

    /** @var boolean */
    protected $hasDefault = false;

    /** @var string */
    protected $default;
    protected $name;

    /** @var Command */
    protected $command;

    /** @var int */
    protected $type = self::TYPE_STRING;
    protected $index = 0;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $permission;

    /**
     * If this parameter doesn't resolve into necessary value, then we will try this parameter
     * @var Parameter
     */
    protected $nextParameter;

    public function __construct(string $name, int $type = null, int $index = null) {
        $this->type = $type ?? $this->type;
        $this->name = $name;
        $this->index = $index ?? $this->index;

        $this->setup();
    }

    public static function isSelfPointer($input): bool
    {
        return in_array($input, ["self", "me"], true);
    }

    public function setNext(Parameter $param) : Parameter {
        $this->nextParameter = $param;
        return $this;
    }

    /**
     * @return Parameter|null
     */
    public function getNextParameter() {
        return $this->nextParameter;
    }

    /**
     * Set error messages and other stuff.
     */
    public function setup() {}

    public function setPermission(string $permission) : Parameter {
        $this->permission = $permission;
        return $this;
    }

    public function getPermission() {
        return $this->permission;
    }

    public function testPermission(CommandSender $sender) {
        return $sender->hasPermission($this->getPermission());
    }

    public function isPermissionSet() : bool {
        return $this->permission !== null;
    }

    public function getIndex() : int {
        return $this->index;
    }

    public function setIndex(int $i) {
        $this->index = $i;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function isRequired(CommandSender $sender = null) : bool {
        return !$this->isDefaultValueSet();
    }

    /**
     * Set default value to null, if you want to set this parameter optional or handle default value yourself
     * @param string|null $value
     */
    public function setDefaultValue($value) : Parameter {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    public function isDefaultValueSet() : bool {
        return $this->hasDefault;
    }

    public function getDefaultValue() {
        return $this->default;
    }

    public function setCommand(Command $command) {
        $this->command = $command;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    /**
     * Will do checks only on primitive data types
     * @return bool
     */
    public static function validateInputType($input, int $type) : bool {
        if(!isset(self::PRIMITIVE_TYPES[$type])) return false;
		switch ($type) {
            case self::TYPE_STRING:
                return is_string($input);
            case self::TYPE_BOOLEAN:
                switch(strtolower($input)) {
                    case '1':
                    case 'true':
                    case 'yes':
                    case 'y':
                    case true:

                    case '0':
                    case 'false':
                    case 'no':
                    case 'n':
                    case false:
                        return true;
                    default:
                        return false;
                }
                return false;
            case self::TYPE_DOUBLE:
            case self::TYPE_FLOAT:
                if(strpos($input, ".") === false) return false;
                return is_numeric($input);
            case self::TYPE_INTEGER:
                return is_numeric($input);
        }
		return false;
	}

    public function getTemplate(CommandSender $sender = null) {
        $out = $this->getName();
        if($this->isDefaultValueSet() and $this->getDefaultValue() !== null) {
            $out .= "=".$this->getDefaultValue();
        }
        if($this->isRequired($sender))
            $out = "<".$out.">";
        else
            $out = "[".$out."]";
        return $out;
    }

    public function createErrorMessage(CommandSender $sender, string $value) : Translatable {
        $error = $this->ERROR_MESSAGES;
        if(is_array($error)) {
            $error = $this->ERROR_MESSAGES[$this->type] ?? "generic-error";
        }
        return Localizer::translatable("".$error, [
            "sender" => ($sender instanceof Player ? $sender->getDisplayName() : $sender->getName()),
            "value" => $value,
            "n" => $this->getIndex() + 1
        ]);
    }

    public function isPrimitive() : bool {
        return isset(self::PRIMITIVE_TYPES[$this->type]);
	}

    /*
     * ----------------------------------------------------------
     * ABSTRACT
     * ----------------------------------------------------------
     */

    /**
     * @param string $input
     * @param CommandSender $sender
     * @return mixed
     */
    public function read(string $input, CommandSender $sender = null) {
        switch ($this->type) {
            case self::TYPE_STRING:
                return (string) $input;
            case self::TYPE_INTEGER:
                return (integer) $input;
            case self::TYPE_FLOAT:
                return (float) $input;
            case self::TYPE_BOOLEAN:
                switch ($input) {
                    case '1':
                    case 'true':
                    case 'yes':
                    case 'y':
                        return true;
                    case '0':
                    case 'false':
                    case 'no':
                    case 'n':
                        return false;
                    default:
                        return null;
                }
            default:
                return null;
        }
    }

    /**
     * @param $input
     * @param CommandSender|null $sender
     * @return bool
     */
    public function isValid($input, CommandSender $sender = null) : bool {
        return self::validateInputType($input, $this->type);
    }

    public function hasDefault() : bool {
        return $this->isDefaultValueSet();
    }

    public function isset($ignoreDefault = false) {
        if($ignoreDefault && $this->hasDefault()) {
            return $this->value !== null;
        } else {
            return $this->value !== $this->default;
        }
    }

    public function unset() {
        $this->value = null;
    }

}