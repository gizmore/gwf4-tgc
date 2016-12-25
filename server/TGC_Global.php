<?php
final class TGC_Global
{
 	public static $TICK = 0;
 	private static $INITIAL_SEED = 0;
	public static $PLAYERS = array();
	public static $SEED = 31337;
	
	public static function init($seed)
	{
		self::$TICK = 0;
		self::$SEED = self::$INITIAL_SEED = $seed;
		self::$PLAYERS = array();
	}
	
	public static function rand($min, $max)
	{
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
	
	###############
	### Private ###
	###############
	private static function createPlayer(GWF_User $user)
	{
		return TGC_Player::createPlayer($user);
	}
	
	private static function loadPlayer($name)
	{
		$ename = GDO::escape($name);
		return GDO::table('TGC_Player')->selectFirstObject('*, user_name, user_gender, user_guest_name', "user_name='$ename'", '', '', array('user'));
	}
	
}
