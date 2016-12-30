<?php
class Firebolt extends TGC_Spell
{
	public function getCodename()
	{
		return 'firebolt';
	}
	
	private function damage()
	{
		return $this->level + $this->power * log($this->power);
	}
	
	public function getCode()
	{
		return sprintf('TARGET.giveHP(-%d)', $this->damage());
	}
	
	public function executeSpell()
	{
		$damage = $this->damage();
		$loot = array();
		$killed = TGC_Kill::damage($this->player, $this->target, $damage, $loot);
		$payload = array(
			'damage' => $damage,
			'killed' => $killed, 
			'loot' => $loot,
		);
		$this->executeDefaultCast($loot);
	}
}