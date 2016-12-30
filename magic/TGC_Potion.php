<?php
abstract class TGC_Potion extends TGC_Spell
{
	public function canTargetSelf() { return true; }
	public function canTargetOther() { return true; }
	public function waterCost() { return 1; }

	public function dicePower()
	{
		$this->power = TGC_Logic::dice(1, 20 * $this->level * $this->player->priestLevel());
	}
	
	public function brew()
	{
		if ($this->player->water() >= $this->waterCost())
		{
			$this->player->giveWater($this->waterCost());
			$this->spell();
		}
		else
		{
			$this->player->sendError('ERR_NO_WATER');
		}
	}
	
	public function cast()
	{
		$this->player->sendError('ERR_NO_CAST');
	}
	
#####

	public function executeSpell()
	{
		$this->executeDefaultBrew();
	}
	
}
