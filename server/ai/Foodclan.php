<?php
/**
 * An enemy that is level 1 in everything :)
 * @author gizmore
 */
class TGCAI_Foodclan extends TGC_AIScript
{
	public function random_gold() { return 1; }
	public function random_gender() { return 'male'; }
	public function random_race() { return 'human'; }
	public function random_mode() { return 'attack'; }
	public function random_color() { return 'black'; }
	public function random_element() { return 'fire'; }
	public function random_fighter() { return 0; }
	public function random_ninja() { return 0; }
	public function random_priest() { return 0; }
	public function random_wizard() { return 0; }
	public function random_hp() { return 0; }
	public function random_mp() { return 0; }
	
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
