![Factions - PE](https://raw.githubusercontent.com/BlockHorizons/FactionsPE/reborn/resources/logo.png "FactionsPE Logo")

Hit counter: [![HitCount](http://hits.dwyl.io/BlockHorizons/FactionsPE.svg)](http://hits.dwyl.io/BlockHorizons/FactionsPE)

### Installing
This plugin has not yet been approved on Poggit.

#### Running from Phar file
Grab the latest .phar file from release section and insert into your server's plugin folder. After, restart the server. See more detailed instructions [at the wiki](https://github.com/BlockHorizons/FactionsPE/wiki/Installation) :+1:

Latest builds are available [here](https://poggit.pmmp.io/ci/BlockHorizons/FactionsPE/FactionsPE).
### Running from source
Clone this repository in your plugins folder, and make sure you have DevTools installed.

### PureChat support
There is no official support for FactionsPE. However I've made [fork](https://github.com/Kris-Driv/PureChat) which does support this plugin. 
**NOTE**: ``force-chat-formatter`` must be set to false in order to allow PureChat format your chat instead.

### Features
###### Permissions
* Build: edit terrain
* Pain Build: edit, take damage
* Door: user doors
* Button: use stone buttons
* Level: use levers
* Container: use containers
* Name: set name
* Desc: set description
* MOTD: set motd
* Invite: invite players
* Kick: kick members
* Title: set titles
* Home: teleport home
* Status: show status
* Set Home: set the home
* Deposit: deposit money
* Withdraw: withdraw money
* Territory: claim or unclaim
* Access: grant territory
* Claim Near: claim nearby
* Relation: change relationships
* Disband: disband the faction
* Perms: manage permissions
* Flags: manage flags
* Fly: fly in faction territory

These permissions are managed by `/faction permission` command. Permissions can be granted to individual ranks
therefore factions can individually decide what their members can and can not do.

###### Flags
* open: Anyone can join. No invite required. (YES)
* permanent: The faction can not be deleted. (NO)
* peaceful: The faction relations work as usual. (NO)
* inf_power: The faction has unlimited power (NO)
* power_loss: Power is lost on death in this territory (YES)
* pvp: You can PVP in this territory (YES)
* friendly_fire: Friendly fire is enabled (NOO) (not editable)
* monsters: Monsters can spawn in this territory (NOO) (not editable)
* animals: Animals can spawn in this territory (YES)
* explosions: Explosions can occur in this territory (YES)
* offline_explosions: No explosions if faction is offline (NO)
* fire_spread: Fire can spread in this territory (YES)
* ender_grief: Endermen can grief this territory (NO)
* zombie_grief Zombies can break doors in this territory (NO)

Control factions behaviour. Manage this flags using `/faction flags` command

###### Relationships
* Ally
* Neutral
* Truce
* Enemy

You can declare relationships with other clans. Relationships play major role in a gameplay. See: `/faction <relation> <member|faction>`

###### Faction's Wallet
Members can invest into faction and contribute to economic progress of the faction. By default, only leader can manage
these funds. See permissions: deposit, withdraw. See: `/faction deposit` `/faction withdraw` `/faction balance`

###### Power system
Implementation of power system enables, cost of plot, killing and dying. If your faction power level goes too
low, you won't be able to hold the claimed land and might lose territory. Be careful when engaging in pvp.

###### Role system
This enables to possibility to demote and promote members of the faction. This is closely related to relationships and
permissions. See: `/faction promote` `/faction demote` `/faction recruit` `/faction leader` `/faction officer`

### For developers
API methods and examples can be found [at the wiki](https://github.com/BlockHorizons/FactionsPE/wiki/API)

### Third-party libraries
+ [Localizer](https://github.com/Kris-Driv/Localizer)
+ [Economizer](https://github.com/Kris-Driv/Economizer)
+ [Dominate](https://github.com/Kris-Driv/Dominate)