<?php
class TGCAI_Nimda 
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
	
	public function tick($tick)
	{
		$this->bot->moveNear($this->bestHuman('score_humanLeader'));
		$this->bot->moveNear($this->bestPlayer('score_humanLeader'));
	}
}
