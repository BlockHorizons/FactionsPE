<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\data\provider;

use fpe\data\MemberData;

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