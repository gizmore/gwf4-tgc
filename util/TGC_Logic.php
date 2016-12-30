<?php
final class TGC_Logic
{
	public static function dice($min=1, $max=6)
	{
		return GWF_Random::rand($min, $max); // Here you are, God of random :)
	}
	
	public static function levelForXP($xp)
	{
		$maxLevel = count(TGC_Const::$LEVELS);
		$level = round(log($xp, 2.41)-5);
		return Common::clamp($level, 0, $maxLevel);
	}
	
	################
	### Position ###
	################
	public static function isPlayerNear(TGC_Player $p, $lat, $lng)
	{
		return self::arePositionsNearEachOther($p->lat(), $p->lng(), $lat, $lng, $p->radius());
	}
	
	public static function arePlayersNearEachOther(TGC_Player $a, TGC_Player $b)
	{
		return self::arePositionsNearEachOther($a->lat(), $a->lng(), $b->lat(), $b->lng(), $a->radius());
	}
	
	public static function arePositionsNearEachOther($latA, $lngA, $latB, $lngB, $maxDistance)
	{
		$distance = TGC_Position::distanceCalculation($latA, $lngA, $latB, $lngB);
		return $distance <= $maxDistance ? $distance : false;
	}
	
	public static function calcRadius(TGC_Player $player)
	{
		return $player->dexterity() * 4.0;
	}
	
	public static function forPlayersNear($lat, $lng, $callback)
	{
		foreach (TGC_Global::$PLAYERS as $player)
		{
			if ($distance = self::isPlayerNear($player, $lat, $lng))
			{
				call_user_func($callback, $player, $distance);
			}
		}
	}
	
}
