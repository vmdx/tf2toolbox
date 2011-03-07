<?php

$PROMO_WEAPONS_DICT = array(
  "Lugermorph", "Frying Pan", "Iron Curtain", "Golden Wrench", "Big Kill", "Enthusiast's Timepiece"
);

$PROMO_HATS_DICT = array(
  "Alien Swarm Parasite", "Foster's Facade", "Stockbroker's Scarf", "Ellis' Cap", "The Athletic Supporter",
  "The Superfan", "The Essential Accessories", "Dealer's Visor", "License to Maim", "Dangeresque, Too?", "Companion Cube Pin",
  "Bounty Hat", "Treasure Hat", "Lumbricus Lid", "Mann Co. Cap", "Ghastlier Gibus",
  "World Traveler's Hat", "Ghastly Gibus", "Mildly Disturbing Halloween Mask", "Saxton Hale Mask", 
  "Scout Mask", "Soldier Mask", "Pyro Mask", "Demoman Mask", "Heavy Mask", "Engineer Mask", "Medic Mask", "Sniper Mask", "Spy Mask"
);

$HIGH_PROMO_HATS_DICT = array(
  "Bill's Hat", "Max's Severed Head", "Hat of Undeniable Wealth And Respect", "Earbuds", "Voodoo Juju", "Spine-Chilling Skull",
  "Gentle Manne's Service Medal", "Hero's Hachimaki"
);

$POLYCOUNT_SET_HATS_DICT = array(
  "Milkman", "Familiar Fez", "Attendant", "Grenadier's Softcap", "Ol' Snaggletooth"
);

$XMAS2010_HATS_DICT = array(
  "Flipped Trilby", "Defiant Spartan", "Exquisite Rack", "Madame Dixie", "Prancer's Pride", "Pyromancer's Mask", "Prince Tavish's Crown",
  "Scotch Bonnet", "Big Chief", "Coupe D'isaster", "Magnificent Mongolian", "Buckaroos Hat", "Industrial Festivizer", "Berliner's Bucket Helm",
  "Blighted Beak", "German Gonzila", "Larrikin Robin", "Détective Noir", "Le Party Phantom", "A Rather Festive Tree"
);

$HAT_BLACKLIST = array(
  "Cheater's Lament", "Primeval Warrior", "Grizzled Veteran", "Soldier of Fortune", "Mercenary", "Bronze Dueling Badge", "Silver Dueling Badge",
  "Gold Dueling Badge", "Platinum Dueling Badge"
);

$PAINT_MAP = array(
  "Paint Can" => "[color=#000000]Error - no color paint!",
  "Indubitably Green" => "[color=#729e42]Indubitably Green",
  "Zephaniah's Greed" => "[color=#424f3b]Zephaniah's Greed",
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
  "Team Spirit" => "[color=#6885a2]Team [/color][color=#b8383b]Spirit"
);

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
