<?php
class TGC_Spell
{
	public $type;
	public $level; // 1st rune
	public $runes;
	public $cost;

	public $player;
	public $target;
	public $areaTarget;
	public $isAreaTarget;
	
	public $power;    // %2$s
	public $powerMultiplier = 1.0;
	public $effect;   // %3$s
	public $duration; // %4$s

	public $mid;
	
	private static $m;
	
	#################
	### Interface ###
	#################
	public function getCode() { return ''; } # JS Code
	public function getXP() { return ceil($this->power + 5); }
	
	public function getCodename() { return $this->getSpellName(); }
	public function getCodenameLowercase() { return strtolower($this->getCodename()); }

	public function canTargetSelf() { return true; }
	public function canTargetArea() { return true; }
	public function canTargetOther() { return true; }

	public function ownMessage() { return self::$m->lang('spell_'.$this->getCodenameLowercase().'_own', $this->defaultMessageArgs()); }
	public function meMessage() { return self::$m->lang('spell_'.$this->getCodenameLowercase().'_me', $this->defaultMessageArgs()); }
	public function otherMessage() { return self::$m->lang('spell_'.$this->getCodenameLowercase().'_other', $this->defaultMessageArgs()); }
	public function defaultMessageArgs() { return array($this->getSpellName(), $this->player->getName(), $this->target->getName(), $this->power, $this->effect, $this->duration); }

	##############
	### Getter ###
	##############
	public function valid() { return $this->runes !== false; }
	public function getSkill() { return $this->type === 'BREW' ? 'priest' : 'wizard'; }
	public function getSpellName() { return implode('', array_slice($this->runes, 1)); }
	public function power() { return $this->power * $this->powerMultiplier; }
	public function playerLevel() { return $this->player->wizard() + ceil($this->player->priest()/4); }
	public function appropiate() { return Common::clamp( ($this->playerLevel() / ($this->level + 3.0)), 0.0, 1.0); }
	
	###############
	### Factory ###
	###############
	public static function init()
	{
		self::$m = Module_Tamagochi::instance();
		GWF_File::filewalker(GWF_PATH.'module/Tamagochi/magic/potion', array(__CLASS__, 'loadSpell'));
		GWF_File::filewalker(GWF_PATH.'module/Tamagochi/magic/spell', array(__CLASS__, 'loadSpell'));
	}
	
	public static function loadSpell($entry, $path)
	{
		require_once $path;
	}
	
	public static function validRunes(array $runes)
	{
		$row = 0;
		foreach ($runes as $rune)
		{
			if (!self::validRune($rune, $row++))
			{
				return false;
			}
		}
		return true;
	}
	
	public static function validRune($rune, $row)
	{
		$runes = self::$m->cfgRunes();
		$len = count($runes[$row]);
		for ($i = 0; $i < $len; $i++)
		{
			if ($runes[$row][$i] === $rune)
			{
				return $i+1;
			}
		}
		return false;
	}
	
	public static function factory(TGC_Player $player, $target, $type, $runes, $mid)
	{
		$runes = explode(',', preg_replace('/[^A-Z,]/', '', strtoupper($runes)));
		$withoutFirst = array_slice($runes, 1);
		if (self::validRunes($runes))
		{
			$runecfg = self::$m->cfgRuneconfig();
			$valid = $runecfg[$type];
			$classname = isset($valid[implode(',', $withoutFirst)]) ? $valid[implode(',', $withoutFirst)] : __CLASS__;
			$spell = new $classname($player, $target, $type, $runes, $mid);
			if ($spell->valid())
			{
				return $spell;
			}
		}
		return false;
	}
	
	public function __construct(TGC_Player $player, $target, $type, $runes, $mid)
	{
		$this->player = $player;
		$this->target = $target;
		$this->type = $type;
		$this->runes = $this->parseRunes($runes);
		$this->mid = $mid;
		if ($this->isAreaTarget = (!($target instanceof TGC_Player)))
		{
			$this->areaTarget = $target;
		}
		$this->dicePower();
	}
	
	private function parseRunes($runes)
	{
		$back = array();
		$row = 0;
		$this->cost = 0;
		$runecost = self::$m->cfgRunecost();
		foreach ($runes as $rune)
		{
			if (false === ($level = $this->validRune($rune, $row)))
			{
				return false;
			}
			$back[] = $rune;
			if ($row == 0)
			{
				$this->level = $level;
			}
			$this->cost += $runecost[$row][$level-1];
			$row++;
		}
		return $back;
	}

	public function dicePower()
	{
		$this->power = TGC_Logic::dice($this->level, 1 + $this->level + ceil($this->playerLevel() / 3.0));
		$this->power = ceil($this->power * $this->appropiate());
	}
	
	#################
	### Cast Dice ###
	#################
	private function failedOfDifficulty()
	{
		return TGC_Global::rand(1, 1000) > ($this->appropiate() * 1000);
	}
	
	private function giveXP($multi=1.0)
	{
		$this->player->giveXP($this->getSkill(), ceil($this->getXP() * $multi));
	}
	
	############
	### Cast ###
	############
	private function drawMP()
	{
		if ($this->player->mp() >= $this->cost)
		{
			$this->player->giveMP(-$this->cost);
			return true;
		}
		return false;
	}

	private function defaultPayload($json, $message=null, $code='')
	{
		return json_encode(array_merge(array(
			'spell' => $this->getSpellName(),
			'player' => $this->player->getName(),
			'runes' => implode(',', $this->runes),
			'level' => $this->level,
			'power' => $this->power,
			'message' => $message,
			'cost' => $this->cost,
			'code' => $code,
		), $json, $this->targetPayload()));
	}
	
	private function targetPayload()
	{
		return $this->isAreaTarget ? $this->areaTarget : array('target' => $this->target->getName());
	}
	
	public function brew()
	{
		return $this->player->sendError('ERR_NO_BREW');
	}
	
	public function cast()
	{
		$this->spell();
	}
	
	public function spell()
	{
		if (!$this->drawMP())
		{
			return $this->player->sendError('ERR_NO_MP');
		}
		
		if ($this->isAreaTarget)
		{
			if (!$this->canTargetArea())
			{
				return $this->player->sendError('ERR_NO_AREA');
			}
			else
			{
				$this->doAreaCast();
			}
		}
		else if ($this->target === $this->player)
		{
			if (!$this->canTargetSelf())
			{
				$this->player->sendError('ERR_'.$this->type.'_SELF');
			}
			else
			{
				$this->doCast();
			}
		}
		else
		{
			if (!$this->canTargetOther())
			{
				$this->player->sendError('ERR_'.$this->type.'_OTHER');
			}
			else
			{
				$this->doCast();
			}
		}
	}

	public function doCast()
	{
		if ($this->failedOfDifficulty())
		{
			$this->giveXP(0.25);
			$this->player->sendError('ERR_'.$this->type.'_FAILED');
		}
		else
		{
			$this->giveXP(1.00);
			$this->executeSpell();
		}
	}
	
	public function doAreaCast()
	{
		TGC_Logic::forPlayersNear($this->areaTarget['lat'], $this->areaTarget['lng'], array($this, 'doAreaCastFor'));
	}
	
	public function doAreaCastFor(TGC_Player $target, $distanceKM)
	{
		$this->target = $target;
		$this->powerMultiplier = $this->areaDistancePower($distanceKM);
		$this->executeSpell();
	}
	
	public function areaDistancePower($distanceKM)
	{
		$distanceKM = Common::clamp(1/$distanceKM, 0.01, 1.00);
		return pow(1, $distanceKM);
	}

######

	public function executeSpell()
	{
		$this->nothingHappens();
	}
	
	public function nothingHappens()
	{
		if ($this->player === $this->target)
		{
			$this->ownCast($this->getCodename(), self::$m->lang('spell_nothing_own', array($this->getCodename())));
		}
		else
		{
			$this->playerCast($this->getCodename(), self::$m->lang('spell_nothing_me', array($this->getCodename())));
			$this->targetCast($this->getCodename(), self::$m->lang('spell_nothing_other', array($this->getCodename())));
		}
	}
	
	public function executeDefaultBrew($json=array())
	{
		$this->executeDefaultCast($json);
	}
	
	public function executeDefaultCast($json=array())
	{
		if ($this->player === $this->target)
		{
			$this->ownCast($this->getSpellName(), $this->ownMessage(), $this->getCode(), $json);
		}
		else
		{
			$this->playerCast($this->getSpellName(), $this->meMessage(), '', $json);
			$this->targetCast($this->getSpellName(), $this->otherMessage(), $this->getCode(), $json);
		}
	}
	
	public function ownCast($codename, $message=null, $code='', $json=array())
	{
		$payload = $this->defaultPayload($json, $message, $code);
		$this->player->sendCommand('TGC_MAGIC', TGC_Commands::payload($payload, $this->mid));
	
	}
	
	public function playerCast($codename, $message=null, $code='', $json=array())
	{
		return $this->ownCast($codename, $message, $code, $json);
	
	}
	
	public function targetCast($codename, $message=null, $code='', $json=array())
	{
		$payload = $this->defaultPayload($json, $message, $code);
		$this->target->sendCommand('TGC_MAGIC', TGC_Commands::payload($payload, $this->mid));
	
	}
	
}
