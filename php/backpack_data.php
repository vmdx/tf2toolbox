<?php
/* These items will be prefixed with a Clean or Dirty if untradeable or gifted. */
$CLEAN_DIRTY_ITEMS = array(
  "Voodoo Juju", "Spine-Chilling Skull", "Hero's Hachimaki", "Horseless Headless Horsemann's Headtaker", "Sharpened Volcano Fragment", "Sun-on-a-Stick"
);

/* Promotional weapons that are NOT craftable through normal TF2-only means. */
$PROMO_WEAPONS_DICT = array(
  "Lugermorph", "Frying Pan", "Iron Curtain", "Big Kill", "Enthusiast's Timepiece", "Fishcake"
);

/* Promotional hats that are NOT craftable through normal TF2-only means. */
$PROMO_HATS_DICT = array(
  // Alien Swarm
  "Alien Swarm Parasite",
  // Killing Floor
  "Foster's Facade", "Stockbroker's Scarf",
  // Left 4 Dead 2 [bill's - HP]
  "Ellis' Cap", 
  // Monday Night Combat
  "The Athletic Supporter", "The Superfan", "The Essential Accessories", 
  // Poker Night at the Inventory
  "Dealer's Visor", "License to Maim", "Dangeresque, Too?", 
  // Portal 2
  "Companion Cube Pin",
  // Potato Sack
  "Aperture Labs Hard Hat", "Resurrection Associate Pin",
  // SpaceChem
  "SpaceChem Pin",
  // Spiral Knights
  "Spiral Sallet",
  // The Great Steam Treasure Hunt [HOUWAR - HP]
  "Bounty Hat", "Treasure Hat", 
  // Worms
  "Lumbricus Lid",
  // Other
  "Mann Co. Cap", "World Traveler's Hat", 
  // Halloween 2009
  "Ghastly Gibus", "Mildly Disturbing Halloween Mask",
  // Halloween 2010
  "Ghastlier Gibus", "Horseless Headless Horsemann's Head", "Saxton Hale Mask", 
  "Scout Mask", "Soldier Mask", "Pyro Mask", "Demoman Mask", "Heavy Mask", "Engineer Mask", "Medic Mask", "Sniper Mask", "Spy Mask"
);

/* Special, high value promo hats. */
$HIGH_PROMO_HATS_DICT = array(
  // Left 4 Dead 2
  "Bill's Hat", 
  // Sam & Max: The Devil's Playhouse
  "Max's Severed Head",
  // The Great Steam Treasure Hunt
  "Hat of Undeniable Wealth And Respect", 
  // Halloween 2010
  "Voodoo Juju", "Spine-Chilling Skull",
  // Other
  "Earbuds", "Gentle Manne's Service Medal" 
);

/* Hats with set hat properties. */
$SET_HATS_DICT = array(
  // Polycount
  "Milkman", "Familiar Fez", "Attendant", "Grenadier's Softcap", "Ol' Snaggletooth"
);

/* Set to the latest hat set to come out.
   CURRENT: Uber Update Hats */
$NEW_HATS_DICT = array(
  // Uber Update
	"Capo's Capper", "Made Man", "Cosa Nostra Cap", "Desert Marauder", "Sultan's Ceremonial", "Bonk Boy",
	"Jumper's Jeepcap", "Armored Authority", "Fancy Dress Uniform", "Pocket Medic", "Professor Speks"
);

/* Hats that will never appear in the lists (never tradeable, not giftable). */
$HAT_BLACKLIST = array(
  "Cheater's Lament", "Ghastly Gibus", "Mann Co. Cap", "Wiki Cap", "Polycount Pin", "World Traveler's Hat",
  // Employee Badges
  "Primeval Warrior", "Grizzled Veteran", "Soldier of Fortune", "Mercenary", 
  // Dueling Badges
  "Bronze Dueling Badge", "Silver Dueling Badge", "Gold Dueling Badge", "Platinum Dueling Badge",
  // Japan Charity
  "Humanitarian's Hachimaki", "Benefactor's Kanmuri", "Magnanimous Monarch", 
  // Replay Update
  "Frontline Field Recorder", 
  // Kritzkast
  "Lo-Fi Longwave",
	// Uber Update
	"Proof of Purchase"
);

/* Other items that cannot be traded or gifted */
$ITEM_BLACKLIST = array(
  "Golden Wrench", "Saxxy",
  "RIFT Well Spun Hat Claim Code", "Taunt: Director's Vision"
);

/* Paint to hex color mapping */
$PAINT_MAP = array(
  "Paint Can" => "[color=#000000]Error - no color paint!",
  "Indubitably Green" => "[color=#729e42]Indubitably Green",
  "Zephaniah's Greed" => "[color=#424f3b]Zephaniah's Greed",
	# double entry: not sure why the spelling is inconsistent
	"Zepheniah's Greed" => "[color=#424f3b]Zepheniah's Greed",
  "Noble Hatter's Violet" => "[color=#51384a]Noble Hatter's Violet",
  "Color No. 216-190-216" => "[color=#d8bed8]Color No. 216-190-216",
  "A Deep Commitment to Purple" => "[color=#7d4071]A Deep Commitment to Purple",
  "Mann Co. Orange" => "[color=#cf7336]Mann Co. Orange",
  "Muskelmannbraun" => "[color=#a57545]Muskelmannbraun",
  "Peculiarly Drab Tincture" => "[color=#c5af91]Peculiarly Drab Tincture",
  "Radigan Conagher Brown" => "[color=#694d3a]Radigan Conagher Brown",
  "Ye Olde Rustic Colour" => "[color=#7c6c57]Ye Olde Rustic Colour",
  "Australium Gold" => "[color=#e7b53b]Australium Gold",
  "Aged Moustache Grey" => "[color=#7e7e7e]Aged Moustache Grey",
  "An Extraordinary Abundance of Tinge" => "[color=#e6e6e6]An Extraordinary Abundance of Tinge",
  "A Distinctive Lack of Hue" => "[color=#141414]A Distinctive Lack of Hue",
  "Pink as Hell" => "[color=#ff69b4]Pink as Hell",
  "A Color Similar to Slate" => "[color=#2f4f4f]A Color Similar to Slate",
  "Drably Olive" => "[color=#808000]Drably Olive",
  "The Bitter Taste of Defeat and Lime" => "[color=#32cd32]The Bitter Taste of Defeat and Lime",
  "The Color of a Gentlemann's Business Pants" => "[color=#f0e68c]The Color of a Gentlemann's Business Pants",
  "Dark Salmon Injustice" => "[color=#e9967a]Dark Salmon Injustice",
  "Team Spirit" => "[color=#5885a2]Team [/color][color=#b8383b]Spirit",
	// New dual color paints from Uber Update
	"An Air of Debonair" => "[color=#654740]An Air [/color][color=#28394d]of Debonair",
	"Balaclavas are Forever" => "[color=#3b1f23]Balaclavas [/color][color=#18233d]are Forever",
	"Cream Spirit" => "[color=#c36c2d]Cream [/color][color=#b88035]Spirit",
	"Operator's Overalls" => "[color=#483838]Operator's [/color][color=#384248]Overalls",
	"The Value of Teamwork" => "[color=#803020]The Value [/color][color=#256d8d]of Teamwork",
	"Waterlogged Lab Coat" => "[color=#a89a8c]Waterlogged [/color][color=#839fa3]Lab Coat"
);

/* Integer value to paint suffix tag mapping */
$PAINT_NUMBER_MAP = array(
  3100495 => "[color=#2f4f4f]Slate[/color]",
  8208497 => "[color=#7d4071]Purple[/color]",
  1315860 => "[color=#141414]Black[/color]",
  8289918 => "[color=#7e7e7e]Grey[/color]",
  15132390 => "[color=#e6e6e6]White[/color]",
  15185211 => "[color=#e7b53b]Gold[/color]",
  14204632 => "[color=#d8bed8]Pink - 216[/color]",
  15308410 => "[color=#e9967a]Salmon[/color]",
  8421376 => "[color=#808000]Olive[/color]",
  7511618 => "[color=#729e42]Green[/color]",
  5801378 => "[color=#5885a2]Team [/color][color=#b8383b]Spirit[/color]",
  12073019 => "[color=#5885a2]Team [/color][color=#b8383b]Spirit[/color]",
  13595446 => "[color=#cf7336]Orange[/color]",
  10843461 => "[color=#a57545]Muskelmannbraun[/color]",
  5322826 => "[color=#51384a]Violet[/color]",
  12955537 => "[color=#c5af91]Drab[/color]",
  16738740 => "[color=#ff69b4]Pink - Hell[/color]",
  6901050 => "[color=#694d3a]Brown[/color]",
  3329330 => "[color=#32cd32]Lime[/color]",
  15787660 => "[color=#f0e68c]Business Pants[/color]",
  8154199 => "[color=#7c6c57]Rustic[/color]",
  4345659 => "[color=#424f3b]Greed[/color]",
	// New dual color paints from Uber Update
	6637376 => "[color=#654740]Air [/color][color=#28394d]Debonair[/color]",
	2634909 => "[color=#654740]Air [/color][color=#28394d]Debonair[/color]",
	3874595 => "[color=#3b1f23]Balaclavas [/color][color=#18233d]Forever[/color]",
	1581885 => "[color=#3b1f23]Balaclavas [/color][color=#18233d]Forever[/color]",
	12807213 => "[color=#c36c2d]Cream [/color][color=#b88035]Spirit[/color]",
	12091445 => "[color=#c36c2d]Cream [/color][color=#b88035]Spirit[/color]",
	4732984 => "[color=#483838]Operator's [/color][color=#384248]Overalls[/color]",
	3686984 => "[color=#483838]Operator's [/color][color=#384248]Overalls[/color]",
	8400928 => "[color=#803020]Value of [/color][color=#256d8d]Teamwork[/color]",
	2452877 => "[color=#803020]Value of [/color][color=#256d8d]Teamwork[/color]",
	11049612 => "[color=#a89a8c]Lab [/color][color=#839fa3]Coat[/color]",
	8626083 => "[color=#a89a8c]Lab [/color][color=#839fa3]Coat[/color]"
);


/* Effect integer to name map. */
$EFFECT_MAP = array(
  6 => "Green Confetti",
  7 => "Purple Confetti",
  8 => "Haunted Ghosts",
  9 => "Green Energy",
  10 => "Purple Energy",
  11 => "TF Logo",
  12 => "Massed Flies",
  13 => "Burning Flames",
  14 => "Scorching Flames",
  15 => "Searing Plasma",
  16 => "Vivid Plasma",
  17 => "Sunbeams",
  18 => "Peace Sign",
  19 => "Hearts"
);

/* Holds default levels for each weapon. */

$WEAPON_LEVEL_MAP = array(
  
  1 => array("Buffalo Steak Sandvich", "Dalokohs Bar", "Direct Hit", "Normal items", "Rocket Jumper", "Sandvich", "Shortstop", "Sticky Jumper", "Sydney Sleeper", "Your Eternal Reward"),
  5 => array("Ambassador", "Black Box", "Big Kill", "Blutsauger", "Bonk! Atomic Punch", "Brass Beast", "Buff Banner", "Bushwacka", "Claidheamh Mòr", "Cloak and Dagger", "Crit-a-Cola", "Dead Ringer", "Enthusiast's Timepiece", "Eyelander", "Frontier Justice", "Frying Pan", "Homewrecker", "Iron Curtain", "Jarate", "L'Etranger", "Lugermorph", "Mad Milk", "Natascha", "Pain Train", "Powerjack", "Scotsman's Skullcutter", "Scottish Resistance", "Tribalman's Shiv", "Vita-Saw", "Wrangler"),
  7 => array("Killing Gloves of Boxing"),
  8 => array("Kritzkrieg"),
  10 => array("Axtinguisher", "Backburner", "Back Scratcher", "Battalion's Backup", "Chargin' Targe", "Darwin's Danger Shield", "Degreaser", "Equalizer", "Fists of Steel", "Flare Gun", "Force-A-Nature", "Gloves of Running Urgently", "Gunboats", "Huntsman", "Loch-n-Load", "Razorback", "Sharpened Volcano Fragment", "Sun-on-a-Stick", "Ubersaw", "Ullapool Caber", "Warrior's Spirit"),
  15 => array("Amputator", "Crusader's Crossbow", "Gunslinger", "Jag", "Sandman"),
  20 => array("Southern Hospitality"),
  25 => array("Boston Basher", "Candy Cane", "Golden Wrench"),
  42 => array("Holy Mackerel")
  
);


?>
