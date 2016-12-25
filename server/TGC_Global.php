<?php
final class TGC_Global
{
	private static $INITIAL_SEED = 0;
	public static $SEED = 31337;
	public static $TICK = 0;
	public static $BOTS = array(), $HUMANS = array(), $PLAYERS = array();
	public static $AVERAGE = array();
	
	public static function init($seed)
	{
		self::$TICK = 0;
		self::$INITIAL_SEED = self::$SEED = $seed;
		self::$BOTS = array(); self::$HUMANS = array(); self::$PLAYERS = array();
	}

	############
	### Game ###
	############
	public static function tick()
	{
		self::$AVERAGE = array();
		return self::$TICK++;
	}
	
	public static function rand($min, $max)
	{
		return GWF_Random::rand($min, $max);
	}
	
	###############
	### Players ###
	###############
	public static function addPlayer(TGC_Player $player)
	{
		if ($player->isBot())
		{
			self::$BOTS[] = $player;
		}
		else
		{
			self::$HUMANS[] = $player;
		}
	
		self::$PLAYERS[$player->getName()] = $player;
	}
	
	public static function removePlayer($name)
	{
		unset(self::$PLAYERS[$name]);
	}
	
	public static function getOrCreatePlayer(GWF_User $user)
	{
		$name = $user->displayName();
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
			self::$PLAYERS[$name] = $player;
			return $player;
		}
		return false;
	}

	private static function createPlayer(GWF_User $user)
	{
		return TGC_Player::createPlayer($user);
	}
	
	private static function loadPlayer($name)
	{
		return TGC_Player::getByName($name);
	}
	
	###############
	### Average ###
	###############
	public static function average($field)
	{
		if (!isset(self::$AVERAGE[$field]))
		{
			$total = 1;
			$count = count(self::$HUMANS) + 1;
			foreach (self::$HUMANS as $player)
			{
				$total += $player->power($field);
			}
			self::$AVERAGE[$field] = $total / $count;
		}
		return self::$AVERAGE[$field];
	}
}
