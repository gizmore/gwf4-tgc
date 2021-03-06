<?php
final class TGC_Const
{
	const NONE = 'none';
	const GPS_INACCURACY = 100;
	const RADAR_SQUARE = 5000;

	const ATTACK = 'attack';
	const DEFEND = 'defend';
	public static $MODES = array(self::NONE, self::ATTACK, self::DEFEND);
	
	const FIGHTER = 'fighter';
	const NINJA = 'ninja';
	const PRIEST = 'priest';
	const WIZARD = 'wizard';
	public static $SKILLS = array(self::NONE, self::FIGHTER, self::NINJA, self::PRIEST, self::WIZARD);
	
	const RED = 'red';
	const BLACK = 'black';
	const BLUE = 'blue';
	const GREEN = 'green';
	public static $COLORS = array(self::NONE, self::RED, self::BLACK, self::BLUE, self::GREEN);
	
// 	const STEPPE = 'steppe';
// 	const FOREST = 'forest';
// 	const HILLS = 'hills';
// 	const MOUNTAINS = 'mountains';
// 	public static $TERRAINS = array(self::NONE, self::STEPPE, self::FOREST, self::HILLS, self::MOUNTAINS);
	
	const FIRE = 'fire';
	const WIND = 'wind';
	const EARTH = 'earth';
	const WATER = 'water';
	public static $ELEMENTS = array(self::NONE, self::FIRE, self::WIND, self::EARTH, self::WATER);
	
	
	const NEOPHYTE = 'neophyte';
	const NOVICE = 'novice';
	const AMATEUR = 'amateur';
	const APPRENTICE = 'apprentice';
	const ADEPT = 'adept';
	const EXPERT = 'expert';
	const MASTER = 'master';
	const LO_MASTER = 'lo_master';
	const UM_MASTER = 'um_master';
	const ON_MASTER = 'on_master';
	const EE_MASTER = 'ee_master';
	const PA_MASTER = 'pa_master';
	const MON_MASTER = 'mon_master';
	const JEZH_MASTER = 'jezh_master';
	public static $LEVELS = array(self::NONE, self::NEOPHYTE, self::NOVICE, self::AMATEUR, self::APPRENTICE, self::ADEPT, self::EXPERT, self::MASTER, self::LO_MASTER, self::UM_MASTER, self::ON_MASTER, self::EE_MASTER, self::PA_MASTER, self::MON_MASTER, self::JEZH_MASTER);

	public static $RUNES;
	public static $RUNE_COSTS;
}
