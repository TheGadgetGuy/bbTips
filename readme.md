#bbTips v0.4.4

a BBCode Tooltip mod compatible with bbDKP. can also be installed as a standalone mod

WowHead-style Tooltips (Item, Craft, Spell, Quest, Achievements, npc, wowchar, itemset, item icon, item dkp with PTR support.

BBcodes get installed automatically on your Board

#### Example BBcode usage

###Item
`[item gems="40133" enchant="3825"]50468[/item]`

`[item gems="40133" enchant="3825"]50468[/item]`

#######PTR usage
You can prefix the tags with "ptr" to access the Wowhead Public Test Realm database.

Example for PTR 4.2

`[ptritem]Decimation Treads[/ptritem]`

`[ptritem]Sho'ravon, Greatstaff of Annihilation[/ptritem]`
 
###Item icon
 
`[itemico gems="40133" enchant="3825"]50468[/item]`

`[itemico gems="40133" enchant="3825" size=small]Ardent Guard[/itemico]`

`[itemico gems="40133" enchant="3825" size=medium]Ardent Guard[/itemico]`

`[itemico gems="40133" enchant="3825" size=large]Ardent Guard[/itemico]`

PTR

###Itemset

`[itemset]Sanctified Ymirjar Lord's Plate[/itemset]`

###Achievements

`[achievement]Breaking Out of Tol Barad[/achievement]`

`[achievement]4874[/achievement]`

`[ptrachievement]Explore Hyjal[/ptrachievement]`

`[achievement]Loremaster of Outland[/achievement]`

###Spells: 

As of Wow 4.0, the spell ranks were removed. Existing spell tags with spell rank will ignore the rank argument.

`[spell]Power Word: Shield[/spell]`
`[spell]Master of Beasts[/spell]`

Quests

`[quest]A Dire Situation[/quest]`

NPC bbCode

`[npc]Illidan Stormrage[/npc]`


###Crafting bbcode. 
The Craft bbcode can be used with or without the mats argument. The mats argument can be used only once per post.

`[craft mats]Recipe: Dirge's Kickin' Chimaerok Chops[/craft]`

`[craft mats]Design: Brazen Elementium Medallion[/craft]`

`[craft mats]Schematic: Hard Khorium Goggles[/craft]`

`[craft mats]Recipe: Destruction Potion[/craft]`

`[craft]Plans: Black Felsteel Bracers[/craft]`

`[craft mats]Recipe: Vial of the Sands[/craft]`



### Installation
* 	Unzip the zip file into /store/mods/</li>
* 	Launch automod, choose the install link. this will copy all the files, perform the necessary edits. </li>
* 	Then surf to /install/index.php, and you will see the database installer. Launch the database installer.  This will install the acp module, and clear the caches (template, theme, imagesets)
*	Once installed, you will find the ACP module added under the raid section in bbdkp ACP.</li>


### Requirements
*	bbDKP 1.2.6 or higher with Wow installed
*	or phpBB3



