<?php
final class TGC_Race
{
	public static function races() { return array_keys(self::$RACE); }
	public static function enumRaces() { return array_merge(array('none'), self::races()); }
	public static function npcRaces() { return array_slice(self::races(), array_search('gremlin', self::allRaces())+1); }
	public static function playerRaces() { return array_slice(self::races(), 0, array_search('gremlin', self::allRaces())+1); }
	
	
	/**
	 * Bonus values for races.
	 */
	public static $RACE = array(
		'fairy' =>     array('max_hp'=>0,'magic'=> 5,'strength'=>-2,'quickness'=>3,'wisdom'=>4,'intelligence'=>4,'charisma'=> 4,'attack'=>1,'luck'=>3),
		'elve' =>      array('body'=>1,'magic'=> 4,'strength'=>-1,'quickness'=>3,'wisdom'=>2,'intelligence'=>3,'charisma'=> 2,'attack'=>2,'bows'=>1),
		'halfelve' =>  array('body'=>1,'magic'=> 3,'strength'=> 0,'quickness'=>3,'wisdom'=>2,'intelligence'=>2,'charisma'=> 2,'attack'=>3,'bows'=>2),
		'vampire' =>   array('body'=>0,'magic'=> 3,'strength'=> 0,'quickness'=>4,'wisdom'=>2,'intelligence'=>3,'charisma'=> 1,'attack'=>4),
		'darkelve' =>  array('body'=>1,'magic'=> 2,'strength'=> 0,'quickness'=>3,'wisdom'=>2,'intelligence'=>2,'charisma'=> 2,'attack'=>5,'bows'=>2),
		'woodelve' =>  array('body'=>1,'magic'=> 1,'strength'=> 0,'quickness'=>3,'wisdom'=>1,'intelligence'=>2,'charisma'=> 2,'attack'=>6,'bows'=>2),
		'human' =>     array('body'=>2,'magic'=> 0,'strength'=> 0,'quickness'=>3,'wisdom'=>1,'intelligence'=>2,'charisma'=> 2,'attack'=>7),
		'gnome' =>     array('body'=>2,'magic'=> 0,'strength'=> 0,'quickness'=>3,'wisdom'=>1,'intelligence'=>2,'charisma'=> 1,'attack'=>8,'luck'=>1),
		'dwarf' =>     array('body'=>3,'magic'=> 0,'strength'=> 1,'quickness'=>2,'wisdom'=>1,'intelligence'=>2,'charisma'=> 1,'attack'=>9,'luck'=>1),
		'halfork' =>   array('body'=>3,'magic'=>-1,'strength'=> 1,'quickness'=>2,'wisdom'=>1,'intelligence'=>2,'charisma'=> 1,'attack'=>10),
		'halftroll' => array('body'=>3,'magic'=>-2,'strength'=> 2,'quickness'=>2,'wisdom'=>0,'intelligence'=>1,'charisma'=> 0,'attack'=>11),
		'ork' =>       array('body'=>4,'magic'=>-3,'strength'=> 3,'quickness'=>1,'wisdom'=>1,'intelligence'=>1,'charisma'=> 0,'attack'=>12),
		'troll' =>     array('body'=>4,'magic'=>-4,'strength'=> 4,'quickness'=>0,'wisdom'=>0,'intelligence'=>0,'charisma'=> 0,'attack'=>13,'essence'=>-0.2),
		'gremlin' =>   array('body'=>4,'magic'=>-5,'strength'=> 3,'quickness'=>1,'wisdom'=>0,'intelligence'=>0,'charisma'=>-1,'attack'=>14,'reputation'=>2,'essence'=>-0.5),
		#NPC
		'animal' =>    array('body'=>0,'magic'=>0, 'strength'=> 0,'quickness'=>0,'wisdom'=>0,'intelligence'=>0,'charisma'=> 0,'attack'=>5),
		'droid' =>     array('body'=>0,'magic'=>0, 'strength'=> 0,'quickness'=>0,'wisdom'=>0,'intelligence'=>0,'charisma'=>-3,'attack'=>10,'reputation'=>0, 'essence'=>0),
		'dragon' =>    array('body'=>8,'magic'=>8, 'strength'=> 8,'quickness'=>0,'wisdom'=>8,'intelligence'=>8,'charisma'=> 0,'attack'=>15,'reputation'=>12,'essence'=>2),
	);

	/**
	 * Base values for races.
	 */
	public static $RACE_BASE = array(
		'fairy' =>     array('base_hp'=>3, 'base_mp'=>6, 'body'=>1,'magic'=> 1,'strength'=> 0,'quickness'=>3,'wisdom'=> 1,'intelligence'=> 4,'charisma'=> 3,'luck'=>1,'height'=>120,'age'=>  20,'bmi'=> 40), # fairy
		'elve' =>      array('base_hp'=>4, 'base_mp'=>4, 'body'=>1,'magic'=> 1,'strength'=> 0,'quickness'=>3,'wisdom'=> 0,'intelligence'=> 2,'charisma'=> 1,'luck'=>0,'height'=>140,'age'=>  32,'bmi'=> 50), # elve
		'halfelve' =>  array('base_hp'=>5, 'base_mp'=>2, 'body'=>1,'magic'=>-1,'strength'=> 1,'quickness'=>2,'wisdom'=> 0,'intelligence'=> 1,'charisma'=> 1,'luck'=>0,'height'=>160,'age'=>  28,'bmi'=> 60), # halfelve
		'vampire' =>   array('base_hp'=>5, 'base_mp'=>3, 'body'=>0,'magic'=> 1,'strength'=> 2,'quickness'=>2,'wisdom'=> 0,'intelligence'=> 2,'charisma'=> 0,'luck'=>0,'height'=>185,'age'=> 140,'bmi'=> 70), # vampire
		'darkelve' =>  array('base_hp'=>5, 'base_mp'=>1, 'body'=>1,'magic'=>-1,'strength'=> 2,'quickness'=>2,'wisdom'=> 0,'intelligence'=> 1,'charisma'=> 1,'luck'=>0,'height'=>170,'age'=>  26,'bmi'=> 70), # darkelve
		'woodelve' =>  array('base_hp'=>5, 'base_mp'=>2, 'body'=>1,'magic'=>-1,'strength'=> 1,'quickness'=>2,'wisdom'=> 0,'intelligence'=> 1,'charisma'=> 1,'luck'=>0,'height'=>180,'age'=>  24,'bmi'=> 75), # woodelve
		'human' =>     array('base_hp'=>6, 'base_mp'=>0, 'body'=>2,'magic'=>-1,'strength'=> 1,'quickness'=>1,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>185,'age'=>  30,'bmi'=> 80), # human
		'gnome' =>     array('base_hp'=>6, 'base_mp'=>0, 'body'=>2,'magic'=>-1,'strength'=> 1,'quickness'=>1,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>130,'age'=>  32,'bmi'=> 55), # gnome
		'dwarf' =>     array('base_hp'=>6, 'base_mp'=>0, 'body'=>2,'magic'=>-1,'strength'=> 1,'quickness'=>1,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>145,'age'=>  34,'bmi'=> 65), # dwarf
		'halfork' =>   array('base_hp'=>7, 'base_mp'=>-1,'body'=>2,'magic'=>-1,'strength'=> 2,'quickness'=>1,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>195,'age'=>  24,'bmi'=> 80), # halfork
		'halftroll' => array('base_hp'=>8, 'base_mp'=>-2,'body'=>3,'magic'=>-1,'strength'=> 2,'quickness'=>0,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>200,'age'=>  24,'bmi'=> 90), # halftroll
		'ork' =>       array('base_hp'=>9, 'base_mp'=>-3,'body'=>3,'magic'=>-2,'strength'=> 3,'quickness'=>0,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>205,'age'=>  22,'bmi'=>100), # ork
		'troll' =>     array('base_hp'=>10,'base_mp'=>-4,'body'=>3,'magic'=>-2,'strength'=> 3,'quickness'=>0,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>215,'age'=>  18,'bmi'=>110), # troll
		'gremlin' =>   array('base_hp'=>11,'base_mp'=>-6,'body'=>1,'magic'=>-3,'strength'=> 0,'quickness'=>2,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=> 50,'age'=>   1,'bmi'=> 10), # gremlin
		#NPC
		'animal' =>    array('base_hp'=>0, 'base_mp'=>0, 'body'=>0,'magic'=> 0,'strength'=> 0,'quickness'=>0,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>160,'age'=>   2,'bmi'=> 70), # droid
		'droid' =>     array('base_hp'=>0, 'base_mp'=>0, 'body'=>0,'magic'=> 0,'strength'=> 0,'quickness'=>0,'wisdom'=> 0,'intelligence'=> 0,'charisma'=> 0,'luck'=>0,'height'=>160,'age'=>   2,'bmi'=> 70), # droid
		'dragon' =>    array('base_hp'=>0, 'base_mp'=>0, 'body'=>8,'magic'=> 8,'strength'=>12,'quickness'=>3,'wisdom'=>12,'intelligence'=>12,'charisma'=> 0,'luck'=>0,'height'=>500,'age'=>6000,'bmi'=>400), # dragon
	);
}
