<?php
final class TGC_Position
{
	public static function calcRadius(TGC_Player $player)
	{
		return $player->dexterity() * 4.0;
	}
	
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
		$distance = self::distanceCalculation($latA, $lngA, $latB, $lngB);
		return $distance <= $maxDistance ? $distance : false;
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
	
	############
	### Calc ###
	############
	/**
	 * http://assemblysys.com/geographical-distance-calculation-in-php/
	 * 
	 * @param float $point1_lat
	 * @param float $point1_long
	 * @param float $point2_lat
	 * @param float $point2_long
	 * @param string $unit
	 * @param int $decimals
	 * @return float
	 */
	public static function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
		// Calculate the distance in degrees
		$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
	
		// Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
		switch($unit) {
			case 'km':
				$distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
				break;
			case 'mi':
				$distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
				break;
			case 'nmi':
				$distance =  $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
		}
		
// 		printf("Distance: %.02f\n", $distance);
		
		return round($distance, $decimals);
	}
}
