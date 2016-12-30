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
		
// 		if ($this->attacker->lastAction() )
		
		$a = $this->attacker; $d = $this->defender;
		$loot = array();

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
		$slapPower = 10.0 * ($adverbPower/10.0) * ($verbPower/10.0) * ($adjectivePower/10.0) * ($nounPower/10.0);
		$modePower = $this->modePowerMultiplier($a, $d);
		$skillPower = $this->skillPowerMultiplier($a, $d, $skill);
		$colorPower = $this->colorPowerMultiplier($a, $d);
		$elementPower = $this->elementPowerMultiplier($a, $d);
		$power = $slapPower * $modePower * $skillPower * $colorPower * $elementPower;
		printf("%f * %f * %f * %f * %f\n", $slapPower, $modePower, $skillPower, $colorPower, $elementPower);

		# Deal damage
		$damage = round(($power - 5)/10);
		if ($critical = $this->isCriticalHit($a, $d))
		{
			$damage *= 2;
		}
		$damage = min($damage, $d->hp());
		$killed = TGC_Kill::damage($a, $d, $damage, $loot);
		
		# Loot
		$axp = $damage;
		$dxp = ceil($damage / 5);
		$a->giveXP($skill, $axp);
		$d->giveXP($skill, $dxp);
		
		# Announce
		$payload = array(
			'type' => $skill,
			'axp' => $axp,
			'dxp' => $dxp,
			'attacker' => $a->getName(),
			'adverb' => $adverbName,
			'verb' => $verbName,
			'defender' => $d->getName(),
			'adjective' => $adjectiveName,
			'noun' => $nounName,
			'damage' => $damage,
			'critical' => $critical,
			'killed' => $killed,
			'loot' => $loot,
		);
		$payload = TGC_Commands::payload(json_encode($payload), $this->mid);
		$a->sendCommand('TGC_SLAP', $payload);
		$d->sendCommand('TGC_SLAP', $payload);
	}
	
	private function getLoot()
	{
		$loot = array(
			'gold' => TGC_Global::rand(10, 100),
			'food' => TGC_Global::rand(0, 1),
			'water' => TGC_Global::rand(0, 1),
		);
		foreach ($this->defender->getLoot() as $key => $value)
		{
			$loot[$key] = $loot[$key] ? $loot[$key] : 0;
			$loot[$key] += $value;
		}
		return $loot;
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
		switch($am[0].$dm[0])
		{
			case 'aa': return 1.10;
			case 'an': return 1.20;
			case 'ad': return 0.80;
			case 'da': return 0.95;
			case 'dn': return 0.95;
			case 'dd': return 0.95;
			case 'na': return 1.05;
			case 'nn': return 1.05;
			case 'nd': return 0.80;
			default: return 1.00;
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
