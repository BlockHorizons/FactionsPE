<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\engine;

use fpe\FactionsPE;
use fpe\manager\Factions;
use fpe\manager\Members;
use jasonwynn10\ScoreboardAPI\Scoreboard;
use jasonwynn10\ScoreboardAPI\ScoreboardAPI;
use jasonwynn10\ScoreboardAPI\ScoreboardEntry;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class BoardEngine extends Engine implements Listener
{

    private static array $boards = [];
    private static bool $enabled = false;

    private static ScoreboardAPI $api;

    public function __construct(FactionsPE $main, ScoreboardAPI $api, int $loop = -1)
    {
        parent::__construct($main, $loop);

        self::$api = $api;
        self::$enabled = true;

        $this->createBoards();
    }

    private function createBoards(): void
    {
        foreach (Factions::getAll() as $faction) {
            self::createBoard($faction->getId(), $faction->getName());
        }
    }

    public static function createBoard(string $factionId, string $display): ?Scoreboard
    {
        echo "Creating a scoreboard for " . $factionId . PHP_EOL;
        $board = self::$api->createScoreboard(
            $factionId,
            $display,
            Scoreboard::SLOT_SIDEBAR,
            Scoreboard::SORT_ASCENDING
        );
        self::$boards[] = $board;
        return $board;
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    public static function removeBoard(Player $player): void
    {
        $member = Members::get($player);
        $faction = $member->getFaction();
        $board = self::getBoard($faction->getId());
        if ($board) {
            self::$api->removeScoreboard($board, [$player]);
            echo "Board sent!" . PHP_EOL;
        }
    }

    public static function getBoard(string $id): ?Scoreboard
    {
        return self::$api->getScoreboard($id);
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

        if ($member->hasHUD()) {
            self::sendBoard($player);
        }
    }

    public static function sendBoard(Player $player): void
    {
        $member = Members::get($player);
        $faction = $member->getFaction();
        $board = self::getBoard($faction->getId());
        if ($board) {
            self::createEntries($board, $player);

            var_dump($board);
            self::$api->sendScoreboard($board, [$player]);
        }
    }

    public static function createEntries(Scoreboard $board, Player $player): array
    {
        $entries = [];
        for ($i = 1; $i <= 10; $i++) {
            $entry = $board->createEntry($i, $i, ScoreboardEntry::TYPE_FAKE_PLAYER, "line {$i}");
            $entry->pad();

            $entries[] = $entry;
        }

        return $entries;
    }

}