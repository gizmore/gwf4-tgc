<?php
abstract class TGC_Tile
{
	const GEN_WALL    = 0x10000000;
	const GEN_VISITED = 0x20000000;
	
	const HIDE  = 0x01000000;
	const BLOCK = 0x02000000;
	const SWIM  = 0x04000000;

	const UP    = 0x00100000;
	const DOWN  = 0x00200000;
	const STAIR = 0x00400000;
	
	const FIRE  = 0x00010000;
	const WIND  = 0x00020000;
	const EARTH = 0x00040000;
	const WATER = 0x00080000;
	
	const WALL      = 0x03000001;
	const STONE     = 0x00000002;
	const WINDOW    = 0x00000003;
	
	public static function genWall($tile) { return ($tile & self::GEN_WALL) > 0; }
	public static function genVisited($tile) { return ($tile & self::GEN_VISITED) > 0; }
	
	public static function canPass($tile) { return ($tile & self::BLOCK) === 0; }
	public static function canLook($tile) { return ($tile & self::HIDE) === 0; }
	
	
}