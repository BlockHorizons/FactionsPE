CREATE TABLE IF NOT EXISTS `factionspe`.`factions` ( 
	`name` VARCHAR(25) NOT NULL COMMENT 'Name of the faction' ,
	`id` VARCHAR(12) NOT NULL COMMENT 'Unique ID of the faction' , 
	`power` INT NOT NULL DEFAULT '0' , 
	`bank` INT NOT NULL , 
	`m_id` INT NOT NULL ,
	`created` LONG ,
	`description` VARCHAR(255) ,
	`home` BOOLEAN DEFAULT 0 ,
	`home_x` FLOAT ,
	`home_y` FLOAT ,
	`home_z` FLOAT ,
	`home_level` VARCHAR(50)
	) 
ENGINE = InnoDB COMMENT = 'Stores saved faction data';