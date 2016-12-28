<?php
/**
 * Finds a suitable player to attack.
 * @author gizmore
 *
 */
class TGCAI_Robber extends TGC_AIScript
{
	public function score_sittingDuck(TGC_Player $player)
	{
// 		printf("%s is a sitting duck?\n", $player->displayName());
		if ($this->bestKillChancePower($player) > $this->rand(0.0, 0.8))
		{
// 			printf("%s is a sitting duck?\n", $player->displayName());
			if ($this->attraction($player) > $this->rand(0.3, 0.6))
			{
				$score = $this->bestKillChancePower($player) / $player->health();
// 				printf("%s is a sitting duck: %s\n", $player->displayName(), $score);
				return $score;
			}
		}
	}
	
	public function findTarget()
	{
		return $this->bestPlayer('score_sittingDuck');
	}
	
	public function attraction(TGC_Player $player)
	{
		return 1.0;
	}
	
	public function tick($tick)
	{
		if ($target = $this->currentTarget())
		{
			$this->bot->aiAttack($target);
			$this->bot->aiMoveNear($target);
		}
	}
}
