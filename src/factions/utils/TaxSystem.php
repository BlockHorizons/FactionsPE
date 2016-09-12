<?php
namespace factions\utils;

class TaxSystem
{

    const TAX_PLOT = 000;
    const TAX_MEMBER = 001;
    const TAX_OFFICER = 002;
    const TAX_LEADER = 003;

    /** @var TaxSystem $instance */
    private static $instance;

    public function __construct()
    {

    }

    public static function close()
    {
        self::get()->save();
        unset(self::$instance);
    }

    public function save()
    {
    }

    public function get() : TaxSystem
    {
        return self::$instance;
    }

}