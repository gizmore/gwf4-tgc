<?php
class TGCAI_Robber extends TGC_AIScript
{
	/**
	 * Find a likely target
	 * @param TGC_Player $player
	 * @return number
	 */
	public function score_sittingDuck(TGC_Player $player)
	{
		if ($this->bestKillChancePower($player) > $this->rand(0.5, 0.8))
		{
			if ($this->attraction($player) > $this->rand(0.3, 0.6))
			{
				$score = $this->bestKillChanceScore() / $player->health();
				return $score;
			}
		}
	}
	
	protected function attraction(TGC_Player $player)
	{
		return 1.0;
	}

	public function findTarget()
	{
		return $this->bestPlayer('score_sittingDuck');
	}
	
	public function tick($tick)
	{
		$target = $this->currentTarget();
		$this->bot->aiAttack($target);
		$this->bot->aiMoveNear($target);
	}
}
