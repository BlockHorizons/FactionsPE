<?php
namespace factions\utils;

use factions\entity\FPlayer;
use pocketmine\Server;

class HUD
{

    private static $instance = null;

    protected $text = "";
    protected $subTitle = "";
    protected $server;
    /** @var \SplObjectStorage $viewers */
    protected $viewers;

    public function __construct($text, $subTitle = "")
    {
        $this->text = $text;
        $this->subTitle = "";
        $this->server = Server::getInstance();
        $this->viewers = new \SplObjectStorage();
    }

    public static function get() : HUD
    {
        if (!self::$instance) self::$instance = new HUD(Text::getHudText(), Text::getHudSubTitle());
        return self::$instance;
    }

    public function getViewers()
    {
        return $this->viewers;
    }

    public function addViewer(FPlayer $player)
    {
        $this->viewers->attach($player);
    }

    public function removeViewer(FPlayer $player)
    {
        $this->viewers->detach($player);
    }

    public function isViewer(FPlayer $player)
    {
        $this->viewers->contains($player);
    }

    public function clearViewers()
    {
        $this->viewers->removeAllExcept(new \SplObjectStorage());
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function send(FPlayer $player, $text = null, $subTitle = null)
    {
        $text = !$text ? $this->text : $text;
        $subTitle = !$subTitle ? $this->subTitle : $subTitle;

        $player->getPlayer()->sendPopup($this->parse($player, $text), $this->parse($player, $subTitle));
    }

    public function parse(FPlayer $player, $text)
    {
        $pos = $player->getPosition();
        $level = $pos->getLevel();
        $text = str_replace([
            "{WORLD}",
            "{NAME}",
            "{X}",
            "{Y}",
            "{Z}",
            "{SERVER}",
            "{WORLD_PLAYERS}",
            "{SERVER_PLAYERS}",
            "{MAX_PLAYERS}",
            "{FACTION}",
            "{RANK}",
        ], [
            $level->getName(),
            $player->getPlayer()->getDisplayName(),
            $pos->x,
            $pos->y,
            $pos->z,
            $this->server->getName(),
            count($level->getPlayers()),
            count($this->server->getOnlinePlayers()),
            $this->server->getMaxPlayers(),
            $player->hasFaction() ? $player->getFaction()->getName() : Text::parse('no.faction'),
            $player->hasFaction() ? ucfirst(Text::parse('rank.' . $player->getRole())) : Text::parse('no.rank')
        ], $text);
        return $text;
    }


}