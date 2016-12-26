<?php
class TGCAI_Nimda extends TGC_AIScript
{
	/**
	 * Find the best player and try to kill him :)
	 * @param TGC_Player $player
	 * @return int
	 */
	public function score_humanLeader(TGC_Player $player)
	{
		if ($player->sumSkills() >= 0)
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
		if (!($target = $this->bestHuman('score_humanLeader')))
		{
			$target = $this->bestPlayer('score_humanLeader');
		}
		return $target;
	}
	
	public function tick($tick)
	{
		if ($target = $this->currentEnemyTarget())
		{
			$this->moveNear($target, true);
			$this->bruteForce($target);
		}
		else
		{
			$this->heal($this->bot);
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
