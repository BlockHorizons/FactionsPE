<?php
/**
 * Created by PhpStorm.
 * User: primus
 * Date: 6/29/16
 * Time: 3:55 AM
 */

namespace factions\utils;


class UUID {

	const SIZE = 24;
	const SPLITS = 4;

    public static function generate($object) : string {
        $id = substr( md5($object . time()) , 0, 24);
        $every = 24 / 4;
		for($i = 0; $i < 24; $i++) {
			if(!$i) continue;
		   	if($i % $every === 0) $id{$i} = "-";
		}
		return $id;
    }

}