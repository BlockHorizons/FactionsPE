<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 7/2/16
 * Time: 1:19 AM
 */

namespace factions\utils;


use pocketmine\utils\TextFormat;

class ProgressBar
{

    const HEALTH_BAR_CLASSIC = 0x00ec;

    protected $type;
    protected $quota;
    protected $width;

    public function __construct(int $type, int $quota, int $width)
    {
        $this->type = $type;
        $this->quota = $quota;
        $this->width = $width;
    }


    // --------------------------- //
    // GETTERS & SETTERS
    // --------------------------- //

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return ProgressBar
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuota()
    {
        return $this->quota;
    }

    /**
     * @param int $quota
     * @return ProgressBar
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return ProgressBar
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    // ---------------------------------------- //
    // RENDER
    // ---------------------------------------- //


    /**
     *
     */
    public function render() : STRING
    {
        switch ($this->getType()) {
            case self::HEALTH_BAR_CLASSIC:
                #
                # [======          ]
                #

                $ret = "[";
                $left = $this->width - $this->quota;
                $ret .= TextFormat::RED.str_repeat("=", $this->quota).str_repeat(" ", $left).TextFormat::WHITE."]";
                return $ret;

                break;
            default: break;
        }
        return "";
    }
}