<?php
/**
 * Find the best player and try to kill him :)
 * @param TGC_Player $player
 * @return int
 */
class TGCAI_Nimda extends TGC_AIScript
{
	public function random_gold() { return 65535; }
	public function random_gender() { return 'male'; }
	public function random_race() { return 'droid'; }
	public function random_mode() { return 'attack'; }
	public function random_color() { return 'black'; }
	public function random_element() { return 'wind'; }
	public function random_fighter() { return TGC_Levelup::maxLevel(); }
	public function random_ninja() { return TGC_Levelup::maxLevel(); }
	public function random_priest() { return TGC_Levelup::maxLevel(); }
	public function random_wizard() { return TGC_Levelup::maxLevel(); }
	public function random_hp() { return TGC_Levelup::maxLevel(); }
	public function random_mp() { return TGC_Levelup::maxLevel(); }
	
	public function score_humanLeader(TGC_Player $player)
	{
		if ($player->playerLevel() >= 6)
		{
			$score = $player->isHuman() ? 10 : -10;
			$score += $player->sumSkills();
			$score += $player->sumAttributes();
			$score += $player->maxHP();
			$score += round($player->maxMP() / 8);
			return $score;
		}
	}
	
	public function findTarget()
	{
		return $this->bestHuman('score_humanLeader');
	}
	
	public function tick($tick)
	{
		if ($tick % 30)
		{
			if ($target = $this->currentEnemyTarget())
			{
				$this->bruteForce($target);
				$this->moveNear($target, true);
			}
			else
			{
				$this->heal($this->bot);
			}
		}
	}
	
	protected function bruteForce($target)
	{
		if ($target)
		{
			$skill = $this->bestKillChanceSkill($target);
			switch ($skill)
			{
				case 'fighter': $this->fight($target); break;
				case 'ninja': $this->attack($target); break;
				case 'priest':
				case 'wizard':
			}
		}
	}
}
