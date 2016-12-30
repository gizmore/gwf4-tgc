<?php
class Confuse extends TGC_Spell
{
	public function getSpellName()
	{
		return 'Confuse';
	}

	public function getCode()
	{
		return <<< CONFUSE
(function(target) {
	if (target.EVIL_CONFUSE > 10) {
		target.EVIL_CONFUSE += {$this->power()} / 2;
	}
	else {
		target.EVIL_CONFUSE = $this->power;
	}
	function confuse(target) {
		speed = RandUtil.rand(400, 1600);
		MapUtil.setHeading(RandUtil.rand(0, 360));
		MapUtil.panTo(MapUtil.randomLatLng(), speed);
		if (target.EVIL_CONFUSE) {
			target.EVIL_CONFUSE--;
			setTimeout(confuse.bind(this, target), speed);
		}
	} confuse(target);
	
}(TARGET));
CONFUSE;
	}
	
}
