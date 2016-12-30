<?php
final class HealPotion extends TGC_Potion
{
	public function getCodename() { return 'healpotion'; }
	
	public function healHP()
	{
		return $this->power;
	}
	
	public function getCode()
	{
		return sprintf('TARGET.giveHP(%d);', $this->healHP());
	}
	
	public function executeSpell()
	{
		$this->target->giveHP($this->healHP());
		$this->executeDefaultBrew();
	}
}