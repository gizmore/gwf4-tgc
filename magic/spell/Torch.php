<?php
class Torch extends TGC_Spell
{
	public function getSpellName()
	{
		return 'torch';
	}
	
	public function getCode()
	{
		return '';
	}
	
	public function executeSpell()
	{
		$payload = array(
// 			'brave' => $this->power,
		);
		$this->executeDefaultCast($payload);
	}
}