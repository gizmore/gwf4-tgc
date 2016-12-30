<?php
final class TGC_Kill
{
	public static function damage(TGC_Player $attacker, TGC_Player $defender, $damage, array &$loot)
	{
		$defender->giveHP(-$damage);
		if ($killed = $defender->isDead())
		{
			$defender->killedBy($attacker);
			$newLoot = self::generateLoot();
			self::addLoot($newLoot, $defender->getLoot());
			self::addLoot($loot, $newLoot);
			$attacker->giveLoot($newLoot);
		}
		return $killed;
	}
	
	private static function generateLoot()
	{
		return array(
			'water' => TGC_Global::rand(0, 1),
			'food' => TGC_Global::rand(0, 1),
			'gold' => TGC_Global::rand(1, 20),
		);
	}

	private static function addLoot(array &$loot, array $add)
	{
		foreach ($add as $key => $value)
		{
			$loot[$key] = isset($loot[$key]) ? $loot[$key] : 0;
			$loot[$key] += $value;
		}
	}
	
}