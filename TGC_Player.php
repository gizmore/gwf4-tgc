<?php
class TGC_Player extends GDO
{
	public static $JOINS = array('user');
	public static $USER_FIELDS = array('user_name', 'user_guest_name', 'user_gender', 'user_regdate', 'user_level', 'user_credits');
	public static function userFields() { return '*, '.implode(',', self::$USER_FIELDS); }
	
	private $user = null;
	private $lat = null, $lng = null, $moved = false;
	
	private $hp, $mp, $maxHP, $maxMP;
	private $strength, $dexterity, $wisdom, $intelligence;
	private $fighter,  $ninja,     $priest, $wizard;
	
	private $lastAttack;
	private $attacked = false, $defended = false;
	
	public function getClassName() { return __CLASS__; }
	public function getTableName() { return GWF_TABLE_PREFIX.'tgc_players'; }
	public function getColumnDefines()
	{
		return array(
			'p_uid' => array(GDO::PRIMARY_KEY|GDO::UINT),
				
			'p_last_slap' => array(GDO::DATE, GDO::NULL, 14),
				
			'p_active_color' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$COLORS),
			'p_active_element' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$ELEMENTS),
			'p_active_skill' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$SKILLS),
			'p_active_mode' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$MODES),

			'p_last_color_change' => array(GDO::DATE, GDO::NULL, 14),
			'p_last_element_change' => array(GDO::DATE, GDO::NULL, 14),
			'p_last_skill_change' => array(GDO::DATE, GDO::NULL, 14),
			'p_last_mode_change' => array(GDO::DATE, GDO::NULL, 14),

			'p_base_hp' => array(GDO::UINT, 20),
			'p_base_mp' => array(GDO::UINT, 0),
				
			'p_strength' => array(GDO::UINT, 1),
			'p_dexterity' => array(GDO::UINT, 1),
			'p_wisdom' => array(GDO::UINT, 1),
			'p_intelligence' => array(GDO::UINT, 1),
				
			'p_fighter_xp' => array(GDO::UINT, 0),
			'p_ninja_xp' => array(GDO::UINT, 0),
			'p_priest_xp' => array(GDO::UINT, 0),
			'p_wizard_xp' => array(GDO::UINT, 0),

			'p_fighter_level' => array(GDO::UINT, 0),
			'p_ninja_level' => array(GDO::UINT, 0),
			'p_priest_level' => array(GDO::UINT, 0),
			'p_wizard_level' => array(GDO::UINT, 0),
				
			'user' => array(GDO::JOIN, GDO::NOT_NULL, array('GWF_User', 'p_uid', 'user_id')),
		);
	}
	
	##############
	### Static ###
	##############
	public static function getByID($userid)
	{
		$fields = self::$USER_FIELDS;
		$where = 'p_id'.intval($userid);
		$player = self::table(__CLASS__)->selectFirstObject($fields, $where, self::$JOINS);
		$player->afterLoad();
		return $player;
	}
	
	############
	### User ###
	############
	public function getUser() { return $this->user; }
	public function setUser(GWF_User $user) { $this->user = $user; }
	
	###############
	### Getters ###
	###############
	public function getName() { return $this->getVar('user_name'); }
	public function getGender() { return $this->getVar('user_gender'); }
	public function isBot() { return $this->getUser()->isBot(); }

	public function lat() { return $this->lat; }
	public function lng() { return $this->lng; }
	public function hasPosition() { return $this->lat !== null; }
	
	public function isDead() { return $this->hp <= 0; }
	public function giveHP($hp) { $this->hp = Common::clamp($this->hp + $hp, 0, $this->maxHP); }
	public function giveMP($mp) { $this->mp = Common::clamp($this->mp + $mp, 0, $this->maxMP); }
	
	public function hp() { return $this->hp; }
	public function maxHP() { return $this->maxHP; }

	public function mp() { return $this->mp; }
	public function maxMP() { return $this->maxMP; }
	
	public function sumSkills() { return $this->fighter() + $this->ninja() + $this->priest() + $this->wizard(); }
	public function sumAttributes() { return $this->strength() + $this->dexterity() + $this->wisdom() + $this->intelligence(); }

	public function fighterXP() { return $this->xp('fighter'); }
	public function ninjaXP() { return $this->xp('ninja'); }
	public function priestXP() { return $this->xp('priest'); }
	public function wizardXP() { return $this->xp('wizard'); }
	public function xp($skill) { return (int) $this->getVar('p_'.$skill.'_xp'); }
	
	public function fighterLevel() { return $this->level('fighter'); }
	public function ninjaLevel() { return $this->level('ninja'); }
	public function priestLevel() { return $this->level('priest'); }
	public function wizardLevel() { return $this->level('wizard'); }
	public function skillLevel($skill) { return (int) $this->getVar('p_'.$skill.'_level'); }
	
	##############
	### Fields ###
	##############
	public function strength() { return $this->strength; }
	public function dexterity() { return $this->dexterity; }
	public function wisdom() { return $this->wisdom; }
	public function intelligence() { return $this->intelligence; }
	
	public function fighter() { return $this->fighter; }
	public function ninja() { return $this->ninja; }
	public function priest() { return $this->priest; }
	public function wizard() { return $this->wizard; }
	
	public function sober() { return 1.0; }
	public function awake() { return 1.0; }
	public function drought() { return 1.0; }
	public function satiness() { return 1.0; }
	
	#############
	### Score ###
	#############
	public function power($field) { return call_user_func(array($this, $field)); }
	public function compareTo(TGC_Player $player, $field) { return $this->compare($this->power($field), $player->power($field)); }
	public function compareAvg($field) { return $this->compare($this->power($field), $this->average($field)); }
	public function compare($p1, $p2) { return ($p1 - $p2) / ($p1 + $p2); }
	public function average($field) { return TGC_Global::average($field); }
	
	###########
	### DTO ###
	###########
	public function ownPlayerDTO() { return array_merge($this->userDTO(), $this->positionDTO(), $this->statsDTO(), $this->ownStatsDTO()); }
	public function otherPlayerDTO() { return array_merge($this->userDTO(), $this->statsDTO()); }
	public function userPositionDTO() { return array_merge($this->userDTO(), $this->positionDTO()); }
	
	public function userDTO()
	{
		return $this->getGDODataFields(self::$USER_FIELDS);
	}
	
	public function positionDTO()
	{
		return array(
			'lat' => $this->lat,
			'lng' => $this->lng,
		);
	}
	
	public function statsDTO()
	{
		return array(
			'c' => $this->getVar('p_active_color'),
			'e' => $this->getVar('p_active_element'),
			's' => $this->getVar('p_active_skill'),
			'm' => $this->getVar('p_active_mode'),
			'fl' => (int)$this->getVar('p_fighter_level'),
			'nl' => (int)$this->getVar('p_ninja_level'),
			'pl' => (int)$this->getVar('p_priest_level'),
			'wl' => (int)$this->getVar('p_wizard_level'),
		);
	}
	
	public function ownStatsDTO()
	{
		return array(
// 			'p_uid' => $this->getVar('p_uid'),
			'hp' => $this->hp,
			'mp' => $this->mp,
			'mhp' => $this->maxHP,
			'mmp' => $this->maxMP,
			'as' => (int)$this->getVar('p_strength'),
			'ad' => (int)$this->getVar('p_dexterity'),
			'aw' => (int)$this->getVar('p_wisdom'),
			'ai' => (int)$this->getVar('p_intelligence'),
			'es' => $this->strength,
			'ed' => $this->dexterity,
			'ew' => $this->wisdom,
			'ei' => $this->intelligence,
			'cc' => $this->getVar('p_last_color_change'),
			'ec' => $this->getVar('p_last_element_change'),
			'sc' => $this->getVar('p_last_skill_change'),
			'mc' => $this->getVar('p_last_mode_change'),
			'fx' => (int)$this->getVar('p_fighter_xp'),
			'nx' => (int)$this->getVar('p_ninja_xp'),
			'px' => (int)$this->getVar('p_priest_level'),
			'wx' => (int)$this->getVar('p_wizard_xp'),
		);
	}
	
	##############
	### Create ###
	##############
	public static function getByName($name)
	{
		$ename = GDO::escape($name);
		if ($player = GDO::table(__CLASS__)->selectFirstObject(self::userFields(), "user_name='$ename'", '', '', array('user')))
		{
			$player->afterLoad();
		}
		return $player;
	}
	
	
	public static function createPlayer(GWF_User $user)
	{
		$player = new self(array(
			'p_uid' => $user->getID(),
			'p_base_hp' => '20',
			'p_base_mp' => '0',
			'p_active_color' => TGC_Const::NONE,
			'p_active_element' => TGC_Const::NONE,
			'p_active_skill' => TGC_Const::NONE,
			'p_active_mode' => TGC_Const::NONE,
			'p_last_color_change' => null,
			'p_last_element_change' => null,
			'p_last_skill_change' => null,
			'p_last_mode_change' => null,
			'p_strength' => '1',
			'p_dexterity' => '1',
			'p_wisdom' => '1',
			'p_intelligence' => '1',
			'p_fighter_xp' => '0',
			'p_ninja_xp' => '0',
			'p_priest_xp' => '0',
			'p_wizard_xp' => '0',
			'p_fighter_level' => '0',
			'p_ninja_level' => '0',
			'p_priest_level' => '0',
			'p_wizard_level' => '0',
		));
		$player->insert();
		foreach (self::$USER_FIELDS as $field)
		{
			$player->setVar($field, $user->getVar($field));
		}
		$player->afterLoad();
		return $player;
	}
	
	##################
	### Connection ###
	##################
	public function sendError($i18nKey)
	{
		GWF_Log::logCron(sprintf("%s: %s", $this->getName(), $i18nKey));
		return $this->sendCommand('ERR', $i18nKey);
	}
	
	public function sendJSONCommand($command, $object)
	{
		return $this->sendCommand($command, json_encode($object));
	}
	
	public function sendCommand($command, $payload)
	{
		return $this->send("$command:$payload");
	}
	
	public function send($messageText)
	{
		GWS_Global::send($this->user, $messageText);
	}

	public function disconnect()
	{
		$this->user = null;
		$this->lat = null;
		$this->lng = null;
	}
	
	###################
	### For Near me ###
	###################
	public function isNearMe(TGC_Player $player)
	{
		if ( ($player == $this) || (!$this->hasPosition()) || (!$player->hasPosition()) ) {
			return false;
		}
		return TGC_Logic::arePlayersNearEachOther($this, $player);
	}
	
	public function forNearMe($callback, $payload)
	{
		foreach (TGC_Global::$PLAYERS as $name=> $player)
		{
			if ($this->isNearMe($player))
			{
				call_user_func($callback, $player, $payload);
			}
		}
	}
	
	###########
	### API ###
	###########
	public function moveTo($newLat, $newLng)
	{
		$newLat = (float)$newLat;
		$newLng = (float)$newLng; 
		if ( (!$newLat) || (!$newLng) || $newLat < -90 || $newLat > 90 ||$newLng < -180 || $newLng > 180)
		{
			return false;
		}
		$this->setPosition($newLat, $newLng);
		$this->moved = true;
		return true;
	}
	
	public function setPosition($lat, $lng)
	{
		$this->lat = $lat;
		$this->lng = $lng;
	}
	
	public function getStatsHash()
	{
		$g = substr($this->getVar('user_gender'), 0, 1);
		$sum = $this->getVar('p_fighter_level') + $this->getVar('p_ninja_level') + $this->getVar('p_priest_level') + $this->getVar('p_wizard_level');
		return sprintf('%s%d', $g, $sum);
	}
	
	
	##############
	### Rehash ###
	##############
	public function afterLoad()
	{
		$this->rehash();
		$this->hp = $this->maxHP;
		$this->mp = $this->maxMP;
	}
	
	public function rehash()
	{
		$this->rehashSkills();
		$this->rehashAtrributes();
		$this->rehashStats();
	}
	
	private function rehashSkills()
	{
		$this->rehashSkill('fighter');
		$this->rehashSkill('ninja');
		$this->rehashSkill('priest');
		$this->rehashSkill('wizard');
		$this->fighter = $this->fighterLevel();
		$this->ninja = $this->ninjaLevel();
		$this->priest = $this->priestLevel();
		$this->wizard = $this->wizardLevel();
	}
	
	private function rehashAtrributes()
	{
		$this->strength = $this->getVar('p_strength') + $this->fighterLevel();
		$this->dexterity = $this->getVar('p_dexterity') + $this->ninjaLevel();
		$this->wisdom = $this->getVar('p_wisdom') + $this->priestLevel();
		$this->intelligence = $this->getVar('p_intelligence') + $this->wizardLevel();
	}
	
	private function rehashStats()
	{
		$this->maxHP = $this->getVar('p_base_hp') + $this->strength * 3 + $this->dexterity * 1;
		$this->maxMP = $this->getVar('p_base_mp') + $this->wisdom * 1 + $this->intelligence * 2;
	}
	
	private function rehashSkill($skill)
	{
		$xp = $this->getVar(sprintf('p_%s_xp', $skill));
		$levelvar = sprintf('p_%s_level', $skill);
		$oldLevel = (int) $this->getVar($levelvar);
		$newLevel = TGC_Logic::levelForXP($xp);
		if ($oldLevel !== $newLevel)
		{
			return $this->saveVar($levelvar, $newLevel.'');
		}
		return false;
	}
	
	###############
	### Levelup ###
	###############
	public function giveXP($skill, $xp, $mid=TGC_Commands::DEFAULT_MID)
	{
		$this->increase('p_'.$skill.'_xp', $xp);
		if ($this->rehashSkill($skill))
		{
			$this->onLevelChanged($skill, $mid);
		}
	}

	private function onLevelChanged($skill, $mid)
	{
		# Every level gives slight base hp/mp
		$gain_hp = TGC_Global::rand(1, 3);
		$gain_mp = TGC_Global::rand(0, 1);
		
		# And bonus based on skill
		$gain_str = $gain_dex = $gain_wis = $gain_int = 0;
		switch($skill)
		{
		case 'fighter':
			$gain_hp += TGC_Global::rand(1, 3);
			$gain_mp += TGC_Global::rand(0, 1);
			$gain_str += TGC_Global::rand(1, 2);
			$gain_dex += TGC_Global::rand(0, 1);
			break;

		case 'ninja':
			$gain_hp += TGC_Global::rand(1, 2);
			$gain_mp += TGC_Global::rand(0, 1);
			$gain_str += TGC_Global::rand(0, 2);
			$gain_dex += TGC_Global::rand(0, 3);
			break;
		
		case 'priest':
			$gain_hp += TGC_Global::rand(1, 2);
			$gain_mp += TGC_Global::rand(1, 2);
			$gain_wis += TGC_Global::rand(1, 2);
			$gain_int += TGC_Global::rand(1, 2);
			break;
		
		case 'wizard':
			$gain_hp += TGC_Global::rand(0, 1);
			$gain_mp += TGC_Global::rand(1, 4);
			$gain_wis += TGC_Global::rand(1, 3);
			$gain_int += TGC_Global::rand(1, 3);
			break;
		}

		$this->increase('p_base_hp', $gain_hp); $this->increase('p_base_mp', $gain_mp);
		$this->increase('p_strength', $gain_str); $this->increase('p_dexterity', $gain_dex);
		$this->increase('p_wisdom', $gain_wis);   $this->increase('p_intelligence', $gain_int);
		
		$this->rehash();
		
		$this->giveHP($gain_hp); $this->giveMP($gain_mp);
		
		# Tell player
		$newLevel = $this->getVar('p_'.$skill.'_level');
		$payload = array_merge($this->ownPlayerDTO(), array(
			'skill' => $skill,
			'level' => $newLevel,
		));
		$payload = TGC_Commands::payload(json_encode($payload), $mid);
		$this->sendCommand('LVLUP', $payload);
	}
	
}
