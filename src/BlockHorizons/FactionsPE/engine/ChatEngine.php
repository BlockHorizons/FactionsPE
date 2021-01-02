<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\engine;

use _64FF00\PureChat\PureChat;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Gameplay;
use BlockHorizons\FactionsPE\utils\Text;
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
        $this->format = (bool)$this->getMain()->getConfig()->get("force-chat-formatter", false);
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
            // We define chat format here, but apply it later down
            $format = Gameplay::get("chat.faction-chat", self::FACTION_CHAT_FORMAT);
        }
        // Format will be applied if it's enabled or required by /f chat
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

    /**
     * @return PureChat|null
     */
    public function getPureChat()
    {
        return $this->pureChat;
    }

    /**
     * @param PureChat $pc
     */
    public function setPureChat($pc)
    {
        $this->pureChat = $pc;
    }

    // /**
    //  * Updates player's nametag
    //  *
    //  * @param MembershipChangeEvent $event
    //  * @priority HIGHEST
    //  * @ignoreCancelled true
    //  */
    // public function onMembershipChange(MembershipChangeEvent $event) 
    // {
    //     if($this->getPureChat()) {
    //         $event->getMember()->getPlayer()->setNameTag($this->getPureChat()->getNametag($event->getMember()->getPlayer(), null));
    //     }
    // }

}