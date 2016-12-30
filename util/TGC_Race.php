<?php
final class TGC_Race
{
	public static function races() { return array_keys(self::$RACE); }
	public static function enumRaces() { return array_merge(array('none'), self::races()); }
	public static function npcRaces() { return array_slice(self::races(), array_search('gremlin', self::allRaces())+1); }
	public static function playerRaces() { return array_slice(self::races(), 0, array_search('gremlin', self::allRaces())+1); }
	public static function randomHumanRace() { return TGC_Global::randomItem(self::playerRaces()); }
	public static function validPlayerRace($race) { return in_array($race, self::playerRaces(), true); }
	public static function getBonus($race) { return self::$RACE[$race]; }
	public static function isMagicRace($race) { $b = self::getBonus($race); return $b['max_mp'] > 0; }
	
	/**
	 * Bonus values for races.
	 */
	public static $RACE = array(
		'fairy' =>     array('max_hp'=>0,'max_mp'=> 5,'strength'=>-2,'dexterity'=>3,'wisdom'=>4,'intelligence'=>4),
		'elve' =>      array('max_hp'=>1,'max_mp'=> 4,'strength'=>-1,'dexterity'=>3,'wisdom'=>2,'intelligence'=>3),
		'halfelve' =>  array('max_hp'=>1,'max_mp'=> 3,'strength'=> 0,'dexterity'=>3,'wisdom'=>2,'intelligence'=>2),
		'vampire' =>   array('max_hp'=>0,'max_mp'=> 3,'strength'=> 0,'dexterity'=>4,'wisdom'=>2,'intelligence'=>3),
		'darkelve' =>  array('max_hp'=>1,'max_mp'=> 2,'strength'=> 0,'dexterity'=>3,'wisdom'=>2,'intelligence'=>2),
		'woodelve' =>  array('max_hp'=>1,'max_mp'=> 1,'strength'=> 0,'dexterity'=>3,'wisdom'=>1,'intelligence'=>2),
		'human' =>     array('max_hp'=>2,'max_mp'=> 0,'strength'=> 0,'dexterity'=>3,'wisdom'=>1,'intelligence'=>2),
		'gnome' =>     array('max_hp'=>2,'max_mp'=> 0,'strength'=> 0,'dexterity'=>3,'wisdom'=>1,'intelligence'=>2),
		'dwarf' =>     array('max_hp'=>3,'max_mp'=> 0,'strength'=> 1,'dexterity'=>2,'wisdom'=>1,'intelligence'=>2),
		'halfork' =>   array('max_hp'=>3,'max_mp'=>-1,'strength'=> 1,'dexterity'=>2,'wisdom'=>1,'intelligence'=>2),
		'halftroll' => array('max_hp'=>3,'max_mp'=>-2,'strength'=> 2,'dexterity'=>2,'wisdom'=>0,'intelligence'=>1),
		'ork' =>       array('max_hp'=>4,'max_mp'=>-3,'strength'=> 3,'dexterity'=>1,'wisdom'=>1,'intelligence'=>1),
		'troll' =>     array('max_hp'=>4,'max_mp'=>-4,'strength'=> 4,'dexterity'=>0,'wisdom'=>0,'intelligence'=>0),
		'gremlin' =>   array('max_hp'=>4,'max_mp'=>-5,'strength'=> 3,'dexterity'=>1,'wisdom'=>0,'intelligence'=>0),
		#NPC
		'animal' =>    array('max_hp'=>1,'max_mp'=>5, 'strength'=> 1,'dexterity'=>0,'wisdom'=>0,'intelligence'=>0),
		'droid' =>     array('max_hp'=>4,'max_mp'=>0, 'strength'=> 2,'dexterity'=>0,'wisdom'=>0,'intelligence'=>0),
		'dragon' =>    array('max_hp'=>8,'max_mp'=>8, 'strength'=> 8,'dexterity'=>0,'wisdom'=>8,'intelligence'=>8),
	);
}
