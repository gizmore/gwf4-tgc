<?php
class TGC_Player extends GDO
{
	public static $USER_FIELDS = array('user_name', 'user_guest_name', 'user_gender', 'user_regdate', 'user_level', 'user_credits');
	public static function userFields() { return '*, '.implode(',', self::$USER_FIELDS); }
	public static $JOINS = array('user');

	public static $STATS = array('max_hp', 'max_mp', 'gold');
	public static $SKILLS = array('fighter', 'ninja', 'priest', 'wizard');
	public static $XP = array('fighter_xp', 'ninja_xp', 'priest_xp', 'wizard_xp');
	public static $ATTRIBUTES = array('strength', 'dexterity', 'wisdom', 'intelligence');
	public static function allFields() { return array_merge(self::$XP, self::$SKILLS, self::$STATS, self::$ATTRIBUTES); }
	
	public static $FEELS = array('health', 'endurance', 'sober', 'awake', 'brave', 'satiness', 'drought');
	private $water = 100, $tired = 0, $food = 100, $alc = 0, $frightened = 0, $endurance = 0;
	
	private $user = null, $baseLevel = 1, $adjustedLevel = 1;
	private $lat = null, $lng = null, $radius = 10000.0, $moved = false;
	private $base = array();
	private $effects = array();
	private $adjusted = array();
	
	public function getClassName() { return __CLASS__; }
	public function getTableName() { return GWF_TABLE_PREFIX.'tgc_players'; }
	public function getColumnDefines()
	{
		return array(
			'p_uid' => array(GDO::PRIMARY_KEY|GDO::UINT),
			'p_type' => array(GDO::VARCHAR|GDO::ASCII|GDO::CASE_S, GDO::NULL, 16),
			'p_race' => array(GDO::ENUM, TGC_Const::NONE, TGC_Race::enumRaces()),

			# Base
			'p_gold' => array(GDO::UINT, 50),

			'p_max_hp' => array(GDO::MEDIUM|GDO::UINT, 10),
			'p_max_mp' => array(GDO::MEDIUM|GDO::UINT, 0),
				
			'p_strength' => array(GDO::MEDIUM|GDO::UINT, 0),
			'p_dexterity' => array(GDO::MEDIUM|GDO::UINT, 0),
			'p_wisdom' => array(GDO::MEDIUM|GDO::UINT, 0),
			'p_intelligence' => array(GDO::MEDIUM|GDO::UINT, 0),
			
			'p_fighter' => array(GDO::TINY|GDO::UINT, 0),
			'p_ninja' => array(GDO::TINY|GDO::UINT, 0),
			'p_priest' => array(GDO::TINY|GDO::UINT, 0),
			'p_wizard' => array(GDO::TINY|GDO::UINT, 0),
				
			# Combat				
			'p_fighter_xp' => array(GDO::UINT, 0),
			'p_ninja_xp' => array(GDO::UINT, 0),
			'p_priest_xp' => array(GDO::UINT, 0),
			'p_wizard_xp' => array(GDO::UINT, 0),
				
			'p_active_color' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$COLORS),
			'p_active_element' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$ELEMENTS),
			'p_active_skill' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$SKILLS),
			'p_active_mode' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$MODES),
			
			# Timestamps
			'p_last_color_change' => array(GDO::UINT, GDO::NULL),
			'p_last_element_change' => array(GDO::UINT, GDO::NULL),
			'p_last_skill_change' => array(GDO::UINT, GDO::NULL),
			'p_last_mode_change' => array(GDO::UINT, GDO::NULL),
			'p_last_activity' => array(GDO::UINT, GDO::NULL),

			# Joins
			'user' => array(GDO::JOIN, GDO::NOT_NULL, array('GWF_User', 'p_uid', 'user_id')),
		);
	}
	
	##############
	### Static ###
	##############
	
	############
	### User ###
	############
	public function getUserID() { return $this->getVar('p_uid'); }
	public function setUser(GWF_User $user) { $this->user = $user; }
	public function getUser() { return $this->user; }
	public function isBot() { return $this->user->isBot(); }
	public function isHuman() { return !$this->user->isBot(); }
	public function displayName() { return $this->getUser()->displayName(); }

	###############
	### Friends ###
	###############
	public function isEnemy(TGC_Player $player) { return !$this->isFriendly($player); }
	public function isFriendly(TGC_Player $player) { return $player === $this || $this->isFriend($player); }
	public function isFriend(TGC_Player $player) { return GWF_Friendship::areFriendsByID($this->getUserID(), $player->getUserID()); }
	
	###############
	### Getters ###
	###############
	public function getName() { return $this->getVar('user_name'); }
	public function getRace() { $race = $this->getVar('p_race'); return $race === 'none' ? 'human' : $race; }
	public function getGender() { return $this->getVar('user_gender'); }
	public function isMagicRace() { return TGC_Race::isMagicRace($this->getRace()); }
	
	public function lat() { return $this->lat; }
	public function lng() { return $this->lng; }
	public function hasPosition() { return !!$this->lat; }
	
	public function isDead() { return $this->hp() <= 0; }
	public function giveHP($hp) { $this->base['hp'] = Common::clamp($this->hp() + $hp, 0, $this->maxHP()); $this->feel('health'); }
	public function giveMP($mp) { $this->base['mp'] = Common::clamp($this->mp() + $mp, 0, $this->maxMP()); }
	
	public function hp() { return $this->base['hp']; }
	public function maxHP() { return $this->power('max_hp'); }
	public function mp() { return $this->base['mp']; }
	public function maxMP() { return $this->power('max_mp'); }
	
	public function food() { return $this->food; }
	public function giveFood($food) { $this->food = Common::clamp($this->food + $food, 0, $this->maxFood()); }
	public function maxFood() { return 100; }
	public function water() { return $this->water; }
	public function giveWater($water) { $this->water = Common::clamp($this->water + $water, 0, $this->maxWater()); }
	public function maxWater() { return 10 + $this->priestLevel() * 4; }
	public function gold() { return $this->getVar('p_gold'); }
	
	public function sumSkills() { return $this->fighter() + $this->ninja() + $this->priest() + $this->wizard(); }
	public function sumAttributes() { return $this->strength() + $this->dexterity() + $this->wisdom() + $this->intelligence(); }
	public function playerLevel() { return $this->baseLevel; }
	public function adjustedLevel() { return $this->adjustedLevel; }
	public function fighterLevel() { return $this->skillLevel('fighter'); }
	public function ninjaLevel() { return $this->skillLevel('ninja'); }
	public function priestLevel() { return $this->skillLevel('priest'); }
	public function wizardLevel() { return $this->skillLevel('wizard'); }
	public function skillLevel($skill) { return $this->getVar('p_'.$skill); }
	
	public function feel($feel)
	{
		$value = call_user_func(array($this, $feel));
		$this->base[$feel] = $this->adjusted[$feel] = $value;
		return $value;
	}
	
	#############
	### Score ###
	#############
	public function base($field) { return isset($this->base[$field]) ? $this->base[$field] + 1 : 1; }
	public function power($field) { return $this->adjusted[$field]; }
	public function average($field) { return TGC_Global::average($field); }
	
	public function compareTo(TGC_Player $player, $field) { return $this->compare($this->power($field), $player->power($field)); }
	public function compareAvg($field) { return $this->compare($this->power($field), $this->average($field)); }
	public function compare($p1, $p2) { return Common::clamp( ($p1 - $p2) / ($p1 + $p2), 0.01, 1.00); }
	public function adjust($field, $by) { $this->adjusted[$field] += $by; }
	
	##############
	### Fields ###
	##############
	public function strength() { return $this->power('strength'); }
	public function dexterity() { return $this->power('dexterity'); }
	public function wisdom() { return $this->power('wisdom'); }
	public function intelligence() { return $this->power('intelligence'); }
	public function radius() { return $this->radius; }
	
	public function fighter() { return $this->power('fighter'); }
	public function ninja() { return $this->power('ninja'); }
	public function priest() { return $this->power('priest'); }
	public function wizard() { return $this->power('wizard'); }
	
	public function health() { return Common::clamp($this->hp() /  min(30, $this->maxHP()), 0.0, 1.0); }
	public function endurance() { return Common::clamp($this->endurance / 40.0, 0.0, 1.0); }
	public function sober() { return 1.0; }
	public function brave() { return 1.0; }
	public function awake() { return Common::clamp($this->tired - 50 / 100.0, 0.0, 1.0); }
	public function drought() { return Common::clamp($this->water / 100.0, 0.0, 1.0); }
	public function satiness() { return Common::clamp($this->food - 50 / 200.0, 0.0, 1.0); }
	public function giveEndurance($endurance) { $this->endurance = Common::clamp($this->endurance + $endurance, 0.0, $this->dexterity()); }
	
	#############
	### Debug ###
	#############
	public function debugInfo()
	{
		$fields1 = array(
			'fighter_xp',    'ninja_xp',    'priest_xp',    'wizard_xp',
			'fighter',       'ninja',       'priest',       'wizard',
		);
		$fields2 = array(
			'hp',           'max_hp',
			'mp',           'max_mp',
			'strength',     'dexterity',  'wisdom',      'intelligence',
		);
		$fields3 = self::$FEELS;
		return $this->debugInfoFields($fields1).$this->debugInfoFields($fields2).$this->debugInfoFields($fields3);
	}
	
	public function debugInfoFields(array $fields)
	{
		$powers = [];
		foreach ($fields as $field)
		{
			$powers[] = sprintf('%s: %s(%s)', $field, $this->base($field), $this->power($field));
		}
		return sprintf('%s: %s', $this->displayName(), implode(' - ', $powers))."\n";
	}
	
	###########
	### DTO ###
	###########
	public function sendUpdate() { $this->rehash(); $this->sendCommand('TGC_SELF', json_encode($this->ownPlayerDTO())); }
	
	public function ownPlayerDTO() { return array_merge($this->userDTO(), $this->positionDTO(), $this->statsDTO(), $this->ownStatsDTO(), $this->feelsDTO()); }
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
			'hp' => $this->hp(),
			'mp' => $this->mp(),
			'mhp' => $this->maxHP(),
			'mmp' => $this->maxMP(),
			'race' => $this->getRace(),
			
			'ac' => $this->getVar('p_active_color'),
			'ae' => $this->getVar('p_active_element'),
			'as' => $this->getVar('p_active_skill'),
			'am' => $this->getVar('p_active_mode'),

			'blf' => (int)$this->getVar('p_fighter'),
			'bln' => (int)$this->getVar('p_ninja'),
			'blp' => (int)$this->getVar('p_priest'),
			'blw' => (int)$this->getVar('p_wizard'),
		);
	}
	
	public function ownStatsDTO()
	{
		return array(
			'bas' => $this->base('strength'),
			'bad' => $this->base('dexterity'),
			'baw' => $this->base('wisdom'),
			'bai' => $this->base('intelligence'),
			
			'eas' => $this->power('strength'),
			'ead' => $this->power('dexterity'),
			'eaw' => $this->power('wisdom'),
			'eai' => $this->power('intelligence'),
			
			'elf' => $this->power('fighter'),
			'eln' => $this->power('ninja'),
			'elp' => $this->power('priest'),
			'elw' => $this->power('wizard'),
			
			'lcc' => $this->getVar('p_last_color_change'),
			'lce' => $this->getVar('p_last_element_change'),
			'lcs' => $this->getVar('p_last_skill_change'),
			'lcm' => $this->getVar('p_last_mode_change'),
				
			'xf' => (int)$this->getVar('p_fighter_xp'),
			'xn' => (int)$this->getVar('p_ninja_xp'),
			'xp' => (int)$this->getVar('p_priest_xp'),
			'xw' => (int)$this->getVar('p_wizard_xp'),
				
			'rad' => TGC_Position::calcRadius($this),
				
			'g' => $this->gold(),
			'w' => $this->water(),
			'f' => $this->food(),
		);
	}

	public function feelsDTO()
	{
		$back = array();
		foreach (self::$FEELS as $feel)
		{
			$back[$feel] = $this->feel($feel);
		}
		return $back;
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
	
	public static function createBot(GWF_User $user, $type)
	{
		return self::createPlayer($user, 'TGC_Bot', $type);
	}
	
	public static function createPlayer(GWF_User $user, $classname='TGC_Player', $type=null, $race='none')
	{
		$player = new $classname(array(
			'p_uid' => $user->getID(),
			'p_type' => $type,
			'p_race' => $race,
			'p_gold' => '50',
			'p_max_hp' => '4',
			'p_max_mp' => '0',
			'p_strength' => '0',
			'p_dexterity' => '0',
			'p_wisdom' => '0',
			'p_intelligence' => '0',
			'p_fighter' => '0',
			'p_ninja' => '0',
			'p_priest' => '0',
			'p_wizard' => '0',
			'p_fighter_xp' => '0',
			'p_ninja_xp' => '0',
			'p_priest_xp' => '0',
			'p_wizard_xp' => '0',
			'p_active_color' => TGC_Const::NONE,
			'p_active_element' => TGC_Const::NONE,
			'p_active_skill' => TGC_Const::NONE,
			'p_active_mode' => TGC_Const::NONE,
			'p_last_color_change' => null,
			'p_last_element_change' => null,
			'p_last_skill_change' => null,
			'p_last_mode_change' => null,
			'p_last_activity' => null,
		));
		if (!$player->insert())
		{
			return false;
		}
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
	public function sendError($i18nKey) { return $this->sendCommand('ERR', $i18nKey); }
	public function sendJSONCommand($command, $object) { return $this->sendCommand($command, json_encode($object)); }
	public function sendCommand($command, $payload) { return $this->send("$command:$payload"); }
	public function send($messageText) { GWS_Global::send($this->user, $messageText); }

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
		if ( ($player == $this) || (!$this->hasPosition()) || (!$player->hasPosition()) )
		{
			return false;
		}
		return TGC_Position::arePlayersNearEachOther($this, $player);
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
	
	############
	### Move ###
	############
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
	
	##############
	### Rehash ###
	##############
	public function afterLoad()
	{
		$this->base = array();
		$this->base['hp'] = 0;
		$this->base['mp'] = 0;
		$this->adjusted = array();
		$this->rehash();
		$this->respawn();
	}
	
	public function rehash()
	{
		$this->rehashBase();
		$this->rehashSkills();
		$this->rehashAtrributes();
		$this->rehashRace();
		$this->rehashStats();
		$this->rehashFeels();
		$this->rehashLevel();
	}
	
	private function rehashLevel()
	{
		$this->baseLevel = 1;
		foreach (self::$SKILLS as $skill)
		{
			$this->baseLevel += $this->skillLevel($skill);
		}
		$this->adjustedLevel = $this->baseLevel * 4;
		$this->adjustedLevel += $this->sumAttributes();
	}
	
	private function rehashBase()
	{
		foreach ($this->allFields() as $field)
		{
			$this->base[$field] = (int)$this->getVar('p_'.$field);
		}
		
		foreach ($this->base as $field => $value)
		{
			$this->adjusted[$field] = $value;
		}
	}
	
	private function rehashSkills()
	{
		$this->rehashSkill('fighter');
		$this->rehashSkill('ninja');
		$this->rehashSkill('priest');
		$this->rehashSkill('wizard');
	}
	
	private function rehashAtrributes()
	{
		$this->adjusted['strength'] += $this->fighter();
		$this->adjusted['dexterity'] += $this->ninja();
		$this->adjusted['wisdom'] += $this->priest();
		$this->adjusted['intelligence'] += $this->wizard();
	}
	
	private function rehashRace()
	{
		foreach (TGC_Race::getBonus($this->getRace()) as $field => $bonus)
		{
			$this->adjusted[$field] += $bonus;
		}
	}
	
	private function rehashStats()
	{
		$this->adjusted['max_hp'] += $this->strength() * 3 + $this->dexterity() * 1;
		$this->adjusted['max_mp'] += $this->wisdom() * 1 + $this->intelligence() * 2;
	}
	
	private function rehashFeels()
	{
		foreach (self::$FEELS as $feel)
		{
			$this->feel($feel);
		}
	}
	
	private function rehashSkill($skill)
	{
		$xp = $this->getVar('p_'.$skill.'_xp');
		$oldLevel = $this->base[$skill];
		$newLevel = TGC_Levelup::levelForXP($xp);
		if ($oldLevel != $newLevel)
		{
			$this->base[$skill] = $newLevel;
			$this->adjusted[$skill] = $newLevel;
			if ($this->saveVar('p_'.$skill, $newLevel.''))
			{
				return $newLevel - $oldLevel;
			}
		}
		return false;
	}
	
	###############
	### Levelup ###
	###############
	public function giveXP($skill, $xp)
	{
		$xp = (int)$xp;
		if ($xp > 0)
		{
			$this->increase('p_'.$skill.'_xp', $xp);
			if (false !== ($levelDiff = $this->rehashSkill($skill)))
			{
				$levelDiff = (int)Common::clamp($levelDiff, 0);
				for ($i = 0; $i < $levelDiff; $i++)
				{
					TGC_Levelup::onLevelup($this, $skill);
				}
				$payload = json_encode($this->ownPlayerDTO());
				$this->sendCommand('TGC_LVLUP', $payload);
			}
		}
	}

	############
	### Kill ###
	############
	public function deletePlayer()
	{
		if ($this->delete())
		{
			$this->gameOver();
			TGC_Global::removePlayer($this);
			return true;
		}
	}
	
	public function killedBy(TGC_Player $killer)
	{
		$this->respawn();
	}
	
	public function respawn()
	{
		$this->base['hp'] = $this->maxHP();
		$this->base['mp'] = $this->maxMP();
		$this->giveHP(0); $this->giveMP(0);
		$this->water = ceil($this->maxWater()/2);
		$this->food = ceil($this->maxFood()/2);
		$this->endurance = $this->dexterity();
		$this->rehashFeels();
	}
	
	public function getLoot()
	{
		return array();
	}
	
	public function giveLoot(array $loot)
	{
		$this->food += $loot['food'];
		$this->water += $loot['water'];
		return $this->increase('p_gold', $loot['gold']);
	}
	
	public function killXP(TGC_Player $killer)
	{
		if ($killer->isBot())
		{
			return 1 + $this->playerLevel();
		}
		else
		{
			return ceil($this->adjustedLevel() / 5);
		}
	}
	
	public function gameOver()
	{
		$payload = $this->ownPlayerDTO();
		$this->sendCommand('TGC_GAMEOVER', json_encode($payload));
	}
	
	
	############
	### Tick ###
	############
	public function tick($tick)
	{
		$this->giveEndurance($this->ninjaLevel());
		$this->tired = Common::clamp($this->tired+1, 0, 100);
		$this->giveWater(-1);
		$this->giveFood(-1);
		$this->giveHP(ceil($this->fighterLevel()/2));
		$this->giveMP(ceil($this->wizardLevel()/2));
		$this->rehashFeels();
	}
	
}
