<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\engine;

use fpe\entity\FConsole;
use fpe\entity\Member;
use fpe\event\member\MembershipChangeEvent;
use fpe\FactionsPE;
use fpe\manager\Members;
use fpe\utils\Text;
use jasonwynn10\ScoreboardAPI\Scoreboard;
use jasonwynn10\ScoreboardAPI\ScoreboardAPI;
use jasonwynn10\ScoreboardAPI\ScoreboardEntry;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

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

        // Read scoreboard format from config
        self::$title = $main->getConfig()->getNested("scoreboard.title", "");
        self::$factionEntries = $main->getConfig()->getNested("scoreboard.faction", []);
        self::$factionlessEntries = $main->getConfig()->getNested("scoreboard.factionless", []);

        // Parse color tags
        self::$factionEntries = Text::parseColorVarsInArray(self::$factionEntries);
        self::$factionlessEntries = Text::parseColorVarsInArray(self::$factionlessEntries);

        self::$api = $api;
        self::$enabled = true;
    }

    public static function parseLine(string $text, Member $member): string
    {
        $faction = $member->getFaction();
        return str_replace(
            [":faction", ":name"],
            [$faction->getName(), $member->getPlayer()->getDisplayName()],
            $text
        );
    }

    public static function createBoard(Member $member): ?Scoreboard
    {
        $board = self::$api->createScoreboard(
            $member->getPlayer()->getRawUniqueId(),
            "Scoreboard",
            Scoreboard::SLOT_SIDEBAR,
            Scoreboard::SORT_ASCENDING
        );
        self::createEntries($board, $member);

        self::$boards[$member->getPlayer()->getRawUniqueId()] = $board;
        return $board;
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    public static function removeBoard(Member $member): void
    {
        $board = self::getBoard($member->getPlayer()->getRawUniqueId());
        if ($board) {
            self::$api->removeScoreboard($board);
            unset(self::$boards[$member->getPlayer()->getRawUniqueId()]);
        }
    }

    public static function sendBoard(Member $member): void
    {
        $player = $member->getPlayer();
        $board = self::getBoard($player->getRawUniqueId());
        if ($board) {
            self::$api->sendScoreboard($board, [$player]);
        }
    }

    public static function createEntries(Scoreboard $board, Member $member): array
    {
        $entries = [];
        $lineCount = $member->hasFaction() ? count(self::$factionEntries) : count(self::$factionlessEntries);
        for ($i = 0; $i < $lineCount; $i++) {
            $entry = $board->createEntry($i, $i, ScoreboardEntry::TYPE_FAKE_PLAYER, "line {$i}");
            $entry->pad();

            $entries[] = $entry;
        }

        return $entries;
    }

    public static function getBoard(string $id): ?Scoreboard
    {
        return self::$api->getScoreboard($id);
    }

    public function onRun(int $currentTick)
    {
        foreach (Members::getAllOnline() as $member) {
            if($member instanceof FConsole) continue;

            $board = self::getBoard($member->getPlayer()->getRawUniqueId());
            if(!$board) {
                echo "No board found!".PHP_EOL;
                if($member->hasHUD()) {
                    echo "Creating new board for member".PHP_EOL;
                    self::createBoard($member);
                }
                continue;
            }

            $newLines = explode(
                "\n",
                self::parseLine(
                    implode("\n", $member->hasFaction() ? self::$factionEntries : self::$factionlessEntries),
                    $member
                )
            );

            foreach($board->getEntries() as $line => $entry) {
                if(!isset($newLines[$line])) continue;

                $entry->customName = $newLines[$line];
                $board->updateEntry($entry, [$member->getPlayer()]);
            }
        }
    }

    public function memberRoleChange(MembershipChangeEvent $event) {
        echo "Changing board, because player joined or left the faction".PHP_EOL;
        self::removeBoard($event->getMember());
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     * @ignoreCancelled false
     */
    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $member = Members::get($player);

        self::createBoard($member);
        if ($member->hasHUD()) {
            self::sendBoard($member);
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     * @ignoreCancelled false
     */
    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        self::removeBoard(Members::get($event->getPlayer()));
    }


}