<?php
class TGC_Player extends GDO
{
	public static $USER_FIELDS = array('user_name', 'user_guest_name', 'user_gender', 'user_regdate', 'user_level', 'user_credits');
	public static function userFields() { return '*, '.implode(',', self::$USER_FIELDS); }
	public static $JOINS = array('user');

	public static $STATS = array('max_hp', 'max_mp', 'gold');
	public static $SKILLS = array('fighter', 'ninja', 'priest', 'wizard');
	public static $ATTRIBUTES = array('strength', 'dexterity', 'wisdom', 'intelligence');
	public static function allFields() { return array_merge(self::$SKILLS, self::$STATS, self::$ATTRIBUTES); }

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
// 			'p_race' => array(GDO::ENUM, TGC_Const::NONE, TGC_Const::$RACES),

			# Base
			'p_gold' => array(GDO::UINT, 50),

			'p_max_hp' => array(GDO::UINT, 10),
			'p_max_mp' => array(GDO::UINT, 0),
				
			'p_strength' => array(GDO::UINT, 0),
			'p_dexterity' => array(GDO::UINT, 0),
			'p_wisdom' => array(GDO::UINT, 0),
			'p_intelligence' => array(GDO::UINT, 0),
			
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
	
	public function isDead() { return $this->hp <= 0; }
	public function giveHP($hp) { $this->hp = Common::clamp($this->hp + $hp, 0, $this->power('maxHP'); }
	public function giveMP($mp) { $this->mp = Common::clamp($this->mp + $mp, 0, $this->maxMP); }
	
	public function hp() { return $this->hp; }
	public function maxHP() { return $this->hp; }
	public function mp() { return $this->mp; }
	
	public function sumSkills() { return $this->fighter() + $this->ninja() + $this->priest() + $this->wizard(); }
	public function sumAttributes() { return $this->strength() + $this->dexterity() + $this->wisdom() + $this->intelligence(); }

	public function fighterXP() { return $this->xp('fighter'); }
	public function ninjaXP() { return $this->xp('ninja'); }
	public function priestXP() { return $this->xp('priest'); }
	public function wizardXP() { return $this->xp('wizard'); }
	public function xp($skill) { return (int) $this->getVar('p_'.$skill.'_xp'); }
	
	public function fighterLevel() { return $this->skillLevel('fighter'); }
	public function ninjaLevel() { return $this->skillLevel('ninja'); }
	public function priestLevel() { return $this->skillLevel('priest'); }
	public function wizardLevel() { return $this->skillLevel('wizard'); }
	public function skillLevel($skill) { return (int) $this->getVar('p_'.$skill.'_level'); }
	
	##############
	### Fields ###
	##############
	public function health() { return Common::clamp($this->hp() /  $this->maxHP(), 0.0, 1.0); }
	public function endurance() { return Common::clamp($this->endurance / 40.0, 0.0, 1.0); }
	
	public function strength() { return $this->strength; }
	public function dexterity() { return $this->dexterity; }
	public function wisdom() { return $this->wisdom; }
	public function intelligence() { return $this->intelligence; }
	
	public function fighter() { return $this->fighter; }
	public function ninja() { return $this->ninja; }
	public function priest() { return $this->priest; }
	public function wizard() { return $this->wizard; }
	
	public function sober() { return 1.0; }
	public function awake() { return Common::clamp($this->tired - 50 / 100.0, 0.0, 1.0); }
	public function drought() { return Common::clamp($this->water / 100.0, 0.0, 1.0); }
	public function satiness() { return Common::clamp($this->food - 50 / 200.0, 0.0, 1.0); }
	
	#############
	### Debug ###
	#############
	public function debugInfo()
	{
		$fields1 = array(
			'fighterXP',    'ninjaXP',    'priestXP',    'wizardXP',
			'fighterLevel', 'ninjaLevel', 'priestLevel', 'wizardLevel',
			'fighter',      'ninja',      'priest',      'wizard',
		);
		$fields2 = array(
			'hp',           'maxHP',
			'mp',           'maxMP',
			'strength',     'dexterity',  'wisdom',      'intelligence',
		);
		$fields3 = array(
		);
		return $this->debufInfoFields($fields1).$this->debufInfoFields($fields2).$this->debugInfoFields($fields3);
	}
	
	public function debugInfoFields(array $fields)
	{
		$powers = [];
		foreach ($fields as $field)
		{
			$powers[] = sprintf('%s: %s(%s)', $field, $this->base($field), $this->power($field));
		}
		return sprintf('%s: %s', $this->displayName(), implode('; ', $powers1))."\n";
	}
	
	#############
	### Score ###
	#############
	public function base($field) { return isset($this->base[$field]) ? $this->base[$field] + 1 : 1; }
	public function power($field) { return $this->adjusted[$field]; }
	public function average($field) { return TGC_Global::average($field); }

	public function compareTo(TGC_Player $player, $field) { return $this->compare($this->power($field), $player->power($field)); }
	public function compareAvg($field) { return $this->compare($this->power($field), $this->average($field)); }
	public function compare($p1, $p2) { return round(((float)($p1 - $p2)) / ((float)($p1 + $p2)), 2); }
	
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
	
	public static function createBot(GWF_User $user, $type)
	{
		return self::createPlayer($user, 'TGC_Bot', $type);
	}
	
	public static function createPlayer(GWF_User $user, $classname='TGC_Player', $type=null)
	{
		$player = new $classname(array(
			'p_uid' => $user->getID(),
			'p_type' => $type,
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
		$this->rehash();
		$this->hp = $this->maxHP;
		$this->mp = $this->maxMP;
		$this->endurance = $this->dexterity();
		$this->tired = 0;
		$this->food = 1000;
		$this->water = 100;
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
		$this->maxHP = $this->getVar('p_base_hp') + $this->strength() * 3 + $this->dexterity() * 1;
		$this->maxMP = $this->getVar('p_base_mp') + $this->wisdom() * 1 + $this->intelligence() * 2;
	}
	
	private function rehashSkill($skill)
	{
		$xp = $this->getVar(sprintf('p_%s_xp', $skill));
		$oldLevel = $this->base($skill) - 1;
		$newLevel = TGC_Logic::levelForXP($xp);
		if ($oldLevel != $newLevel)
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
		$payload = array_merge($this->ownPlayerDTO(), array(
			'skill' => $skill,
			'level' => $this->base($skill),
		));
		$payload = TGC_Commands::payload(json_encode($payload), $mid);
		$this->sendCommand('TGC_LVLUP', $payload);
	}
	
}
