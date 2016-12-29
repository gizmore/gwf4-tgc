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
	
	private $user = null;
	private $lat = null, $lng = null, $moved = false;
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
// 	public static function getByID($userid)
// 	{
// 		$fields = self::$USER_FIELDS;
// 		$where = 'p_uid'.intval($userid);
// 		$player = self::table(__CLASS__)->selectFirstObject($fields, $where, self::$JOINS);
// 		$player->afterLoad();
// 		return $player;
// 	}
	
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
	public function getRace() { return $this->getVar('p_race'); }
	public function getGender() { return $this->getVar('user_gender'); }
	
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
	
	public function sumSkills() { return $this->fighter() + $this->ninja() + $this->priest() + $this->wizard(); }
	public function sumAttributes() { return $this->strength() + $this->dexterity() + $this->wisdom() + $this->intelligence(); }

// 	public function fighterXP() { return $this->xp('fighter'); }
// 	public function ninjaXP() { return $this->xp('ninja'); }
// 	public function priestXP() { return $this->xp('priest'); }
// 	public function wizardXP() { return $this->xp('wizard'); }
// 	public function xp($skill) { return $this->getVar('p_'.$skill.'_xp'); }
	
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
	
	##############
	### Fields ###
	##############
	public function strength() { return $this->power('strength'); }
	public function dexterity() { return $this->power('dexterity'); }
	public function wisdom() { return $this->power('wisdom'); }
	public function intelligence() { return $this->power('intelligence'); }
	
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
			'fl' => (int)$this->getVar('p_fighter'),
			'nl' => (int)$this->getVar('p_ninja'),
			'pl' => (int)$this->getVar('p_priest'),
			'wl' => (int)$this->getVar('p_wizard'),
		);
	}
	
	public function ownStatsDTO()
	{
		return array(
// 			'p_uid' => $this->getVar('p_uid'),
			'hp' => $this->hp(),
			'mp' => $this->mp(),
			'mhp' => $this->maxHP(),
			'mmp' => $this->maxMP(),
			'as' => (int)$this->getVar('p_strength'),
			'ad' => (int)$this->getVar('p_dexterity'),
			'aw' => (int)$this->getVar('p_wisdom'),
			'ai' => (int)$this->getVar('p_intelligence'),
			'es' => $this->power('strength'),
			'ed' => $this->power('dexterity'),
			'ew' => $this->power('wisdom'),
			'ei' => $this->power('intelligence'),
			'cc' => $this->getVar('p_last_color_change'),
			'ec' => $this->getVar('p_last_element_change'),
			'sc' => $this->getVar('p_last_skill_change'),
			'mc' => $this->getVar('p_last_mode_change'),
			'fx' => (int)$this->getVar('p_fighter_xp'),
			'nx' => (int)$this->getVar('p_ninja_xp'),
			'px' => (int)$this->getVar('p_priest_xp'),
			'wx' => (int)$this->getVar('p_wizard_xp'),
			'r' => TGC_Logic::calcRadius($this),	
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
	
	public static function createBot(GWF_User $user, $type)
	{
		return self::createPlayer($user, 'TGC_Bot', $type);
	}
	
	public static function createPlayer(GWF_User $user, $classname='TGC_Player', $type=null)
	{
		$player = new $classname(array(
			'p_uid' => $user->getID(),
			'p_type' => $type,
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
	
// 	public function getStatsHash()
// 	{
// 		$g = substr($this->getVar('user_gender'), 0, 1);
// 		$sum = $this->getVar('p_fighter_level') + $this->getVar('p_ninja_level') + $this->getVar('p_priest_level') + $this->getVar('p_wizard_level');
// 		return sprintf('%s%d', $g, $sum);
// 	}
	
	##############
	### Rehash ###
	##############
	public function afterLoad()
	{
		$this->base = array();
		$this->base['hp'] = 0;
		$this->base['mp'] = 0;
		foreach ($this->allFields() as $field)
		{
			$this->base[$field] = (int)$this->getVar('p_'.$field);
		}
		$this->adjusted = array();
		$this->rehash();
		$this->respawn();
	}
	
	public function rehash()
	{
		$this->rehashBase();
		$this->rehashSkills();
		$this->rehashAtrributes();
		$this->rehashStats();
		$this->rehashFeels();
	}
	
	private function rehashBase()
	{
		foreach ($this->base as $key => $value)
		{
			$this->adjusted[$key] = $this->base($key);
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
		$newLevel = TGC_Logic::levelForXP($xp);
		if ($oldLevel != $newLevel)
		{
			$this->base[$skill] = $newLevel;
			$this->adjusted[$skill] = $newLevel;
			return $this->saveVar('p_'.$skill, $newLevel.'');
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
		
		$this->increaseVars(array(
			'p_max_hp' => $gain_hp,
			'p_max_mp' => $gain_mp,
			'p_strength' => $gain_str,
			'p_dexterity' => $gain_dex,
			'p_wisdom' => $gain_wis,
			'p_intelligence' => $gain_int,
		));
		
		$this->rehash();
		$this->giveHP($gain_hp); $this->giveMP($gain_mp);
		
		# Tell player
		$payload = json_encode(array_merge($this->ownPlayerDTO(), array(
			'skill' => $skill,
			'level' => $this->base[$skill],
		)));
		$this->sendCommand('TGC_LVLUP', $payload);
	}
	
	############
	### Kill ###
	############
	public function deletePlayer()
	{
		return $this->delete();
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
	
	
	############
	### Tick ###
	############
	public function tick($tick)
	{
		$this->giveEndurance($this->ninjaLevel());
		$this->tired = Common::clamp($this->tired+1, 0, 100);
		$this->food = Common::clamp($this->food-1, 0, 100);
		$this->water = Common::clamp($this->water-1, 0, 100);;
		$this->rehashFeels();
	}
	
}
