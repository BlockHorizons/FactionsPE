<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\data\provider;

use BlockHorizons\FactionsPE\data\MemberData;

trait MemberFilePath
{

    /**
     * @param MemberData|string $member
     * @param $ext with fullstop
     */
    public function getMemberFilePath($member, string $ext)
    {
        $name = strtolower(trim($member instanceof MemberData ? $member->getName() : $member));
        return $this->getMain()->getDataFolder() . "members/" . substr($name, 0, 1) . "/" . $name . $ext;
    }

}