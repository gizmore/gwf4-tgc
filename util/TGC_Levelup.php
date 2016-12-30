<?php
final class TGC_Levelup
{
	public static function m() { return Module_Tamagochi::instance(); }
	public static function levelValues() { return self::m()->cfgLevels(); }
	public static function levels() { return array_keys(self::levelValues()); }
	public static function maxLevel() { return count(self::levels()) - 1; }
	public static function level($level) { return (int)Common::clamp(round($level), 0, self::maxLevel()); }
	public static function levelName($level) { $levelNames = self::levels(); return $levelNames[self::level($level)]; }
	public static function displayLevel($level) { return self::displayLevelName(self::levelName($level)); }
	public static function displayLevelName($levelName) { return self::m()->lang('lvl_'.$levelName); }
	
	##################
	### Level calc ###
	##################
	public static function levelForXP($xp) { return self::lvlForXP($xp, 'level'); }
	public static function levelNameForXP($xp) { return self::lvlForXP($xp, 'name'); }
	private static function lvlForXP($xp, $field)
	{
		$level = -1;
		foreach (self::levelValues() as $name => $xpNeeded)
		{
			if ($xp < $xpNeeded)
			{
				break;
			}
			$level++;
		}
		return $$field;
	}
	
	#############
	### Bonus ###
	#############
	public static function onLevelup(TGC_Player $player, $skill)
	{
		# Every level gives slight base hp/mp
		$gain_hp = TGC_Global::rand(1, $player->isMagicRace()?2:3);
		$gain_mp = TGC_Global::rand(0, $player->isMagicRace()?1:0);
	
		# And bonus based on skill
		$gain_str = $gain_dex = $gain_wis = $gain_int = 0;
		switch($skill)
		{
			case 'fighter':
				$gain_hp += TGC_Global::rand(1, 4);
				$gain_str += TGC_Global::rand(1, 2);
				$gain_dex += TGC_Global::rand(0, 1);
				break;
	
			case 'ninja':
				$gain_hp += TGC_Global::rand(1, 2);
				$gain_mp += TGC_Global::rand(0, 1);
				$gain_str += TGC_Global::rand(0, 2);
				$gain_dex += TGC_Global::rand(0, 3);
				break;
	
			case 'priest':
				$gain_hp += TGC_Global::rand(1, 2);
				$gain_mp += TGC_Global::rand(1, 2);
				$gain_wis += TGC_Global::rand(1, 2);
				$gain_int += TGC_Global::rand(1, 2);
				break;
	
			case 'wizard':
				$gain_hp += TGC_Global::rand(0, 1);
				$gain_mp += TGC_Global::rand(1, 4);
				$gain_wis += TGC_Global::rand(1, 3);
				$gain_int += TGC_Global::rand(1, 3);
				break;
		}
	
		$player->increaseVars(array(
			'p_max_hp' => $gain_hp,
			'p_max_mp' => $gain_mp,
			'p_strength' => $gain_str,
			'p_dexterity' => $gain_dex,
			'p_wisdom' => $gain_wis,
			'p_intelligence' => $gain_int,
		));
	
		$player->rehash();
		$player->giveHP($gain_hp);
		$player->giveMP($gain_mp);
	}
	
}