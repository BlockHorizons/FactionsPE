<?php
use factions\manager\Plots;
use factions\manager\Factions;

Plots::fromHash('4:4:world')->claim(Factions::getByName("test"));