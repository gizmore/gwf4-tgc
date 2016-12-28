<?php
final class TGC_Attack
{
	private $attacker;
	private $defender;
	
	private $mid;
	
	public function __construct(TGC_Player $attacker, TGC_Player $defender, $mid)
	{
		$this->attacker = $attacker;
		$this->defender = $defender;
		
		$this->mid = $mid;
	}
	
	
	public function dice($skill)
	{
		if ($this->attacker === $this->defender)
		{
			return $this->attacker->sendError(TGC_Commands::payload('ERR_ATTACK_SELF', $this->mid));
		}
		
		$a = $this->attacker; $d = $this->defender;

		$am = $a->getVar('p_active_mode'); $dm = $d->getVar('p_active_mode');
		$as = $a->getVar('p_active_skill'); $ds = $d->getVar('p_active_skill');
		$ac = $a->getVar('p_active_color'); $dc = $d->getVar('p_active_color');
		$ae = $a->getVar('p_active_element'); $de = $d->getVar('p_active_element');
		
		$slaps = require(GWF_CORE_PATH.'module/Tamagochi/slapdata/slaps.php');
		
		$adverb = $this->randomItem($slaps['adverbs']);
		$verb = $this->randomItem($slaps['verbs']);
		$adjective = $this->randomItem($slaps['adjectives']);
		$noun = $this->randomItem($slaps['nouns']);
		$adverbName = $adverb[0]; $adverbPower = $adverb[1];
		$verbName = $verb[0]; $verbPower = $verb[1];
		$adjectiveName = $adjective[0]; $adjectivePower = $adjective[1];
		$nounName = $noun[0]; $nounPower = $noun[1];
		
		# Power
		$power = round(1 * ($adverbPower/10.0) * ($verbPower/10.0) * ($adjectivePower/10.0) * ($nounPower/10.0));
		$power *= $this->modePowerMultiplier($a, $d);
		$power *= $this->skillPowerMultiplier($a, $d, $skill);
		$power *= $this->colorPowerMultiplier($a, $d);
		$power *= $this->elementPowerMultiplier($a, $d);
		if ($critical = $this->isCriticalHit($a, $d))
		{
			$power *= 2;
		}

		# Deal damage
		$damage = round($power);
		$d->giveHP(-$damage);
		if ($killed = $d->isDead())
		{
			$d->killedBy($a);
		}
		
		# Tell about slap.
		printf("%s attacks %s with power %s: %s/%sHP left.\n", $a->displayName(), $d->displayName(), $power, $d->hp(), $d->maxHP());
		
		$payload = array(
			'attacker' => $a->displayName(),
			'adverb' => $adverbName,
			'type' => $skill,
			'verb' => $verbName,
			'defender' => $d->displayName(),
			'adjective' => $adjectiveName,
			'noun' => $nounName,
			'critical' => $critical,
			'damage' => $damage,
			'killed' => $killed,
// 			'power' => $power,
// 			'nounPower' => $nounPower,
// 			'adverbPower' => $adverbPower,
// 			'verbPower' => $verbPower,
// 			'adjectivePower' => $adjectivePower,
		);
		$payload = TGC_Commands::payload(json_encode($payload), $this->mid);
		$a->sendCommand('TGC_SLAP', $payload);
		$d->sendCommand('TGC_SLAP', $payload);
		
		# Give XP
		$a->giveXP($skill, $power, $this->mid);
		$d->giveXP($skill, $power/10, $this->mid);
	}
	
	private function randomItem($slaps)
	{
		return $slaps[array_rand($slaps)];
	}
	private function isCriticalHit(TGC_Player $attacker, TGC_Player $defender)
	{
		return TGC_Logic::dice(1, 20) === 20;
	}

	private function modePowerMultiplier(TGC_Player $a, TGC_Player $d)
	{
		$am = $a->getVar('p_active_mode'); $dm = $d->getVar('p_active_mode');
		if (($am == $dm) || ($am === 'none'))
		{
			return 1.00;
		}
		else if ($am == 'attacker')
		{
			return 1.05;
		}
		else
		{
			return 0.95;
		}
	}

	private function skillPowerMultiplier(TGC_Player $attacker, TGC_Player $defender, $skill)
	{
		return $attacker->compareTo($defender, $skill);
// 		$af = $attacker->fighter(); $df = $defender->fighter();
// 		$an = $attacker->ninja();   $dn = $defender->ninja();
// 		$ap = $attacker->priest();  $dp = $defender->priest();
// 		$aw = $attacker->wizard();  $dw = $defender->wizard();
		
// 		switch ($skill)
// 		{
// 		case 'fighter':
// 			$atotal = $af + $an;
// 			break;
// 		case 'ninja':
// 			$dtotal = $df + $dn;
// 			break;
				
// 		case 'priest':
			
// 		case 'wizard':
			
// 		}
// 		return 1.0;
	}

	private function colorPowerMultiplier(TGC_Player $attacker, TGC_Player $defender)
	{
		return 1.0;
	}

	private function elementPowerMultiplier(TGC_Player $attacker, TGC_Player $defender)
	{
		return 1.0;
	}
	
}
