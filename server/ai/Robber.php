<?php
class TGCAI_Robber
{
	/**
	 * Find a likely target
	 * @param TGC_Player $player
	 * @return number
	 */
	public function score_sittingDuck(TGC_Player $player)
	{
		$chance = $this->killChance($player);
		if ($this->killChance($player) > $this->rand(0.5, 0.8))
		{
			if ($this->attraction($player) > $this->rand(0.3, 0.6))
			{
				$score = 100 / $player->health();
	
			}
		}
		$score *= $player->health() / 0.4;
		$score *= $player->comparedToAverage('p_fighter') / 0.5;
		$score *= $player->comparedToAverage('p_ninja') / 0.8;
		return $score;
	}

	public function findTarget()
	{
		return $this->bestPlayer('score_sittingDuck');
	}
	
	public function tick($tick)
	{
		$target = $this->currentTarget();
		$this->bot->moveNear($target);
		$this->bot->attack($target);
	}
}
