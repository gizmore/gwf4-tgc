<?php
abstract class TGC_AIScript
{
	############
	### Init ###
	############
	private static function scriptsPath() { return GWF_PATH.'module/Tamagochi/server/ai'; }
	public static $TYPES = null;
	public static function init()
	{
		if (!self::$TYPES)
		{
			self::$TYPES = array();
			GWF_File::filewalker(self::scriptsPath(), function($entry, $path) {
				require_once $path;
				self::$TYPES[] = Common::substrUntil($entry, '.php', true);
			});
		}
		return self::$TYPES;
	}
	
	###############
	### Factory ###
	###############
	public static function factory(TGC_Bot $bot)
	{
		$type = 'TGCAI_'.$bot->getType();
		return new $type($bot);
	}
	private function __construct($bot) { $this->bot = $bot; $this->difficulty = $this->rand(0.0, 0.05); }
	
	################
	### Abstract ###
	################
	abstract function tick($tick);
	public function findTarget() { return $this->randomPlayer(); }
	
	###########
	### Bot ###
	###########
	protected $bot;
	protected $target;
	protected $difficulty;
	
	protected function hpRate() { return $this->bot->hp() / $this->bot->maxHP(); }
	protected function mpRate() { return $this->bot->mp() / $this->bot->maxMP(); }
	
	protected function players() { return TGC_Global::$PLAYERS; }
	protected function humans() { return TGC_Global::$PLAYERS; }
	protected function bots() { return TGC_Global::$PLAYERS; }

	protected function randomBot() { return $this->randomTarget($this->bots()); }
	protected function randomHuman() { return $this->randomTarget($this->humans()); }
	protected function randomPlayer() { return $this->randomTarget($this->players()); }
	protected function randomTarget(array $targets) { return GWF_Random::arrayItem($targets); }
	
	protected function bestBot($scoreMethod, $topShuffle=3) { return $this->bestTarget($this->bots(), $scoreMethod, $topShuffle); }
	protected function bestHuman($scoreMethod, $topShuffle=3) { return $this->bestTarget($this->humans(), $scoreMethod, $topShuffle); }
	protected function bestPlayer($scoreMethod, $topShuffle=3) { return $this->bestTarget($this->players(), $scoreMethod, $topShuffle); }
	
	##############
	### Target ###
	##############
	protected function findOldTarget() { return TGC_Global::getPlayer($this->target->getName()); }
	protected function selectNewTarget() { $this->target = $this->findTarget(); return $this->target; }
	protected function currentTarget() { return $this->findOldTarget() ? $this->target : $this->selectNewTarget(); }
	
	####################
	### Kill Chances ###
	####################
	protected function killChanceScore(TGC_Player $player)
	{
		$score = 1000 / $player->health();
		$chance = array_pop($this->bestKillChance($player));
		return $score * $chance;
	}
	
	protected function killChance(TGC_Player $player, $skill)
	{
		$cmp_health = $this->bot->compareTo($player, 'health');
		$cmp_power = $this->bot->compareTo($player, $skill);
		return $cmp_power / $cmp_health;
	}
	
	protected function bestKillChanceSkill(TGC_Player $player)
	{
		return array_unshift($this->bestKillChance($player));
	}
	
	protected function bestKillChance(TGC_Player $player)
	{
		$bestSkill = 'fighter';
		$bestChance = $this->killChance($player, 'fighter');
		foreach (TGC_Const::$SKILLS as $skill)
		{
			$chance = $this->killChance($player, $skill);
			if ($chance > $bestChance)
			{
				$bestChance = $chance;
				$bestSkill = $skill;
			}
		}
		return array($bestSkill, $bestChance);
	}
	
	###############
	### Sorting ###
	###############
	private function bestTarget(array $targets, $scoreMethod, $topShuffle=3)
	{
		$possibleTargets = array();
		foreach ($targets as $target)
		{
			$target instanceof TGC_Player;
			if ($score = call_user_func(array($this, $scoreMethod), $target))
			{
				$possibleTargets[$target->getName()] = $score;
			}
		}
		usort($possibleTargets);
		$possibleTargets = slice(array_keys($possibleTargets), 0, $topShuffle);
		shuffle($possibleTargets);
		return array_pop($possibleTargets);
	}
	
	############
	### Rand ###
	############
	public function botrand($zero=0.0, $one=1.0)
	{
		return $this->rand($zero, $one, $this->difficulty);
	}
	public function rand($zero=0.0, $one=1.0, $difficulty=0.0)
	{
		$min = (int)(Common::clamp($zero+$difficulty, 0.0, 1.0) * 1000.0);
		$max = (int)(Common::clamp($one+$difficulty, $min, 1.0) * 1000.0);
		return GWF_Random::rand($min, $max) / 1000.0;
	}
	
}
