<?php
/**
 * Lock a random target and try to kill it.
 * @author gizmore
 */
class TGCAI_Assassin extends TGC_AIScript
{
	public function findTarget()
	{
		return $this->randomHuman();
	}

	public function tick($tick)
	{
		$target = $this->currentTarget();
		$this->bot->aiAttack($target);
		$this->bot->aiMoveNear($target);
	}
}
