<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
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

namespace factions\engine;

use _64FF00\PureChat\PureChat;
use factions\event\member\MembershipChangeEvent;
use factions\manager\Members;
use factions\utils\Gameplay;
use factions\utils\Text;
use localizer\Localizer;
use pocketmine\event\player\PlayerChatEvent;

/**
 * Format the chat and route chat messages
 */
class ChatEngine extends Engine
{

    const PLAYER_FORMAT = "{NAME}: {MESSAGE}";
    const MEMBER_FORMAT = "<gray>[<gold>{BADGE}<white>{FACTION}<gray>]<white> {NAME}: {MESSAGE}";
    const MEMBER_TITLE_FORMAT = "<gray>[<gold>{BADGE}<white>{FACTION}<gray>]<white>[{TITLE}]<white> {NAME}: {MESSAGE}";
    const FACTION_CHAT_FORMAT = "<gray><bold>(<red>fac<gray><bold>) <red>{BADGE}<gray><{NAME}<gray>: <red>{MESSAGE}";

    /** @var bool */
    protected $format;

    /** @var PureChat|null */
    protected $pureChat = null;

    public function setup()
    {
        $this->format = (bool)$this->getMain()->getConfig()->get("chat-formatter", true);
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled false
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $member = Members::get($player);
        $faction = $member->getFaction();
        // Time to handle "faction chat"
        if ($member->isFactionChatOn() && $member->hasFaction()) {
            $p = [];
            foreach ($faction->getOnlineMembers() as $member) {
                $p[] = $member->getPlayer();
            }
            $event->setRecipients(array_merge($p, [Members::get("CONSOLE")]));
            if (count($p) === 1) {

                $member->toggleFactionChat();
                $player->sendMessage(Localizer::translatable("faction-chat-disabled-due-empty"));

                $event->setCancelled(true);
                return;
            }
            $format = Gameplay::get("chat.faction-chat", self::FACTION_CHAT_FORMAT);
        }
        if ($this->format || isset($format)) {
            // Get type of format we need
            if (!isset($format)) {
                $format = Gameplay::get("chat.player", self::PLAYER_FORMAT);
                if ($member->hasFaction()) {
                    if ($member->hasTitle()) {
                        $format = Gameplay::get("chat.member-title", self::MEMBER_TITLE_FORMAT);
                    } else {
                        $format = Gameplay::get("chat.member", self::MEMBER_FORMAT);
                    }
                }
            }
            // Translate color codes
            $format = Text::parse($format);
            // Replace variables
            $format = str_replace(["{PLAYER}", "{NAME}", "{FACTION}", "{ROLE}", "{BADGE}", "{MESSAGE}", "{TITLE}"], [
                $player->getName(),
                $player->getDisplayName(),
                $faction->isNormal() ? $faction->getName() : "nil",
                $r = $member->getRole(),
                self::getBadge($r),
                $event->getMessage(),
                $member->getTitle()
            ], $format);
            $event->setFormat($format);
        }
    }

    public static function getBadge(string $role): string
    {
        return Gameplay::get("chat.badge." . strtolower(trim($role)), "");
    }

    public function setPureChat(PureChat $pc) {
        $this->pureChat = $pc;
    }

    public function getPureChat() {
        return $this->pureChat;
    }

    public function onMembershipChange(MembershipChangeEvent $event) {
        if($this->getPureChat()) {
            $event->getMember()->getPlayer()->setNameTag($this->getPureChat()->getNametag($event->getMember()->getPlayer(), null));
        }
    }

}