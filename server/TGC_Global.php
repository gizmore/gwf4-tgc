<?php
final class TGC_Global
{
	private static $INITIAL_SEED = 0;
	public static $SEED = 31337;
	public static $TICK = 0;
	public static $BOTS = array(), $HUMANS = array(), $PLAYERS = array(), $TYPED_BOTS = array();
	public static $AVG_BASE = array();
	public static $AVG_ADJUST = array();
	
	public static function init($seed)
	{
		self::$TICK = 0;
		self::$INITIAL_SEED = self::$SEED = $seed;
		self::$BOTS = array(); self::$HUMANS = array(); self::$PLAYERS = array();
		foreach (TGC_AIScript::init() as $type)
		{
			self::$TYPED_BOTS[$type] = array();
		}
		
	}

	############
	### Game ###
	############
	public static function tick()
	{
		self::$AVG_BASE = self::$AVG_ADJUST = array();
		return self::$TICK++;
	}
	
	public static function rand($min, $max)
	{
		return GWF_Random::rand($min, $max);
	}
	
	public static function randItem(array $array)
	{
		return $array[array_rand($array)];
	}
	
	###############
	### Players ###
	###############
	public static function addPlayer(TGC_Player $player)
	{
		$name = $player->getName();
		if ($player->isBot())
		{
			self::$BOTS[$name] = $player;
			self::$TYPED_BOTS[$player->getType()][$name] = $player;
		}
		else
		{
			self::$HUMANS[$name] = $player;
		}
		self::$PLAYERS[$name] = $player;
	}

	public static function removePlayer(TGC_Player $player)
	{
		$name = $player->getName();
		if ($player->isBot())
		{
			unset(self::$BOTS[$name]);
			unset(self::$TYPED_BOTS[$player->getType()][$name]);
		}
		else
		{
			unset(self::$HUMANS[$name]);
		}
		unset(self::$PLAYERS[$name]);
	}
	
	public static function getOrCreatePlayer(GWF_User $user)
	{
		$name = $user->getName();
		if (!($player = self::getOrLoadPlayer($name)))
		{
			$player = self::createPlayer($user);
			self::$PLAYERS[$name] = $player;
		}
		$player->setUser($user);
		return $player;
	}
	
	public static function getPlayer($name)
	{
		return isset(self::$PLAYERS[$name]) ? self::$PLAYERS[$name] : false;
	}
	
	public static function getOrLoadPlayer($name)
	{
		if ($player = self::getPlayer($name))
		{
			return $player;
		}
		if ($player = self::loadPlayer($name))
		{
			self::$PLAYERS[$name] = self::$HUMANS[$name] = $player;
			return $player;
		}
		return false;
	}

	private static function createPlayer(GWF_User $user)
	{
		return TGC_PlayerFactory::human($user);
	}
	
	private static function loadPlayer($name)
	{
		return TGC_Player::getByName($name);
	}
	
	###############
	### Average ###
	###############
	public static function averageBase($field)
	{
		if (!isset(self::$AVG_BASE[$field]))
		{
			$total = 1;
			$count = count(self::$HUMANS) + 1;
			foreach (self::$HUMANS as $player)
			{
				$total += $player->base($field);
			}
			self::$AVG_BASE[$field] = round($total / $count);
		}
		return self::$AVG_BASE[$field];
	}
	
	public static function averagePower($field)
	{
		if (!isset(self::$AVG_ADJUST[$field]))
		{
			self::$AVG_ADJUST[$field] = 0;
			$count = count(self::$HUMANS);
			if ($count > 0)
			{
				$total = 0;
				foreach (self::$HUMANS as $player)
				{
					$total += $player->power($field);
				}
				self::$AVG_ADJUST[$field] = round($total / $count);
			}
		}
		return self::$AVG_ADJUST[$field];
	}
}
