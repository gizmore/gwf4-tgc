<?php
final class TGC_Logic
{
	public static function levelForXP($xp)
	{
		$maxLevel = count(TGC_Const::$LEVELS);
		$level = round(log($xp, 2.41)-5);
		return Common::clamp($level, 0, $maxLevel);
	}
	
	public static function arePlayersNearEachOther(TGC_Player $a, TGC_Player $b)
	{
		return self::arePositionsNearEachOther($a->lat(), $a->lng(), $b->lat(), $b->lng());
	}
	
	public static function arePositionsNearEachOther($latA, $lngA, $latB, $lngB)
	{
		return true;
		return TGC_Position::distanceCalculation($latA, $lngA, $latB, $lngB);
	}
	
	public static function dice($min=1, $max=6)
	{
		return GWF_Random::rand($min, $max); // Here you are, God of random :)
	}
	
	public static function calcRadius(TGC_Player $player)
	{
		return $player->dexterity() * 4;
	}
	
}
