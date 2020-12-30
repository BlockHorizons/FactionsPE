<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\engine;

use fpe\command\Relation;
use fpe\entity\FConsole;
use fpe\entity\Member;
use fpe\event\member\MembershipChangeEvent;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\utils\ASCIICompass;
use fpe\utils\Text;
use jasonwynn10\ScoreboardAPI\Scoreboard;
use jasonwynn10\ScoreboardAPI\ScoreboardAPI;
use jasonwynn10\ScoreboardAPI\ScoreboardEntry;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BoardEngine extends Engine implements Listener
{

    private static array $boards = [];
    private static bool $enabled = false;

    private static ScoreboardAPI $api;

    private static string $title = "";
    private static array $factionEntries = [];
    private static array $factionlessEntries = [];

    public function __construct(FactionsPE $main, ScoreboardAPI $api)
    {
        parent::__construct($main, 20);
        self::$api = $api;
        self::$enabled = true;

        // Read scoreboard format from config
        self::$title = $main->getConfig()->getNested("scoreboard.title", "");
        self::$factionEntries = $main->getConfig()->getNested("scoreboard.faction", []);
        self::$factionlessEntries = $main->getConfig()->getNested("scoreboard.factionless", []);

        // Parse color tags
        self::$factionEntries = Text::parseColorVarsInArray(self::$factionEntries);
        self::$factionlessEntries = Text::parseColorVarsInArray(self::$factionlessEntries);

        // Create scoreboards
        self::createBoard("faction", count(self::$factionEntries));
        self::createBoard("factionless", count(self::$factionlessEntries));
    }

    public static function parseLine(string $text, Member $member): string
    {
        $faction = $member->getFaction();
        $player = $member->getPlayer();
        $string = str_replace(
            [
                ":faction",
                ":name",
                ":here-faction",
                "<here-color>",
                ":x", ":y", ":z",
                ":direction",
                ":short-direction",
                ":online-members",
                ":max-members",
                ":player-online",
                ":total-players",
                ":power",
                ":max-power"
            ],
            [
                $faction->getName(),
                $member->getPlayer()->getDisplayName(),
                $member->getFactionHere()->getName(),
                Text::getRelationColor($member->getRelationToPlot()),
                $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(),
                ASCIICompass::getFullDirection($player->getYaw()),
                ASCIICompass::getCompassPointForDirection($player->getYaw()),
                count($faction->getOnlineMembers()),
                count($faction->getMembers()),
                count(Server::getInstance()->getOnlinePlayers()),
                Server::getInstance()->getMaxPlayers(),
                $member->getPower(),
                $member->getPowerMax()
            ],
            $text
        );

        $max = 0;
        $lengths = [];
        $lines = explode("\n", $string);
        foreach($lines as $k => $line) {
            if(($pos = strpos($line, "<padding>")) === false) {
                continue;
            }
            $line = TextFormat::clean($line);
            $pos += 9;
            $c = strlen(substr($line, 0, $pos));
            $lengths[$k] = $c;
            if($c > $max) $max = $c;
        }
        foreach($lengths as $i => $c) {
            $lines[$i] = str_replace("<padding>", str_repeat(" ", $max - $c), $lines[$i]);
        }

        return implode("\n", $lines);
    }

    public static function createBoard(string $id, int $lineCount): ?Scoreboard
    {
        $board = self::$api->createScoreboard(
            $id,
            Text::parse(self::$title),
            Scoreboard::SLOT_SIDEBAR,
            Scoreboard::SORT_ASCENDING
        );
        self::createEntries($board, $lineCount);

        self::$boards[$id] = $board;
        return $board;
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    public static function removeBoard(Member $member): void
    {
        $board = self::getBoard($member);
        self::$api->removeScoreboard($board, [$member->getPlayer()]);
    }

    public static function sendBoard(Member $member, ?string $id = null): void
    {
        $board = self::getBoard($member, $id);
        self::$api->sendScoreboard($board, [$member->getPlayer()]);
    }

    public static function createEntries(Scoreboard $board, int $lineCount): array
    {
        $entries = [];
        for ($i = 0; $i < $lineCount; $i++) {
            $entry = $board->createEntry($i, $i, ScoreboardEntry::TYPE_FAKE_PLAYER, "line {$i}");
            $entry->pad();

            $entries[] = $entry;
        }

        return $entries;
    }

    public static function getBoard(Member $member, ?string $id = null): ?Scoreboard
    {
        return self::$api->getScoreboard($id ?? $member->hasFaction() ? "faction" : "factionless");
    }

    public function onRun(int $currentTick)
    {
        self::$api->removeScoreboard(self::$boards["faction"]);
        self::$api->removeScoreboard(self::$boards["factionless"]);

        foreach (Members::getAllOnline() as $member) {
            if($member instanceof FConsole) continue;
            if(!$member->hasHUD()) continue;

            $board = self::getBoard($member);

            $newLines = explode(
                "\n",
                self::parseLine(
                    implode("\n", $member->hasFaction() ? self::$factionEntries : self::$factionlessEntries),
                    $member
                )
            );

            self::$api->sendScoreboard($board, [$member->getPlayer()]);
            foreach($board->getEntries() as $line => $entry) {
                if(!isset($newLines[$line])) continue;

                $entry->customName = $newLines[$line];
                $board->updateEntry($entry, [$member->getPlayer()]);
            }
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        self::sendBoard(Members::get($event->getPlayer()));
    }

    /**
     * @param MembershipChangeEvent $event
     * @priority MONITOR
     * @ignoreCancelled false
     */
    public function onMembershipChange(MembershipChangeEvent $event) {
        /** @var Member $member */
        $member = $event->getMember();
        self::removeBoard($member);
        self::sendBoard($member, $event->isLeaving() ? "factionless" : "faction");
    }

}