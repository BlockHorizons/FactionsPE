<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\relation;

interface RelationParticipator
{

    public function getRelationTo(RelationParticipator $observer, bool $ignorePeaceful = false): string;

    public function isFriend(RelationParticipator $observer): bool;

    public function isEnemy(RelationParticipator $observer): bool;

    public function getColorTo(RelationParticipator $observer): string;

}