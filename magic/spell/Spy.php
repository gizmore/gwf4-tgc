<?php
class Spy extends TGC_Spell
{
	public function getSpellName()
	{
		return 'spy';
	}

	public function getCode()
	{
		return sprintf('TARGET.update(%s)', json_encode($this->target->ownPlayerDTO()));
	}

}
