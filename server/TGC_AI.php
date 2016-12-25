<?php
require_once 'TGC_AIScript.php';

final class TGC_AI
{
	private static $INSTANCE;
	
	private $handler;
	private $spawncounter = 0;
	private $lastSpawn = null;
	private $typedBots, $scripts;
	
	###############
	### Getters ###
	###############
	public static function instance() { return self::$INSTANCE; }
	public function handler() { return $this->handler; }
	public function tgc() { return Module_Tamagochi::instance(); }
	public function bots() { return $this->bots; }
	public function scripts() { return $this->scripts; }
	
	############
	### Load ###
	############
	public function init($handler)
	{
		self::$INSTANCE = $this;
		$this->handler = $handler;
		$this->spawncounter = 0;
		$this->scripts = TGC_AIScript::init();
		$this->typedBots = array();
		foreach ($this->scripts as $type)
		{
			$this->typedBots[$type] = array();
		}
		$this->loadBots();
	}
	
	public function loadBots()
	{
		$table = GDO::table('TGC_Bot');
		$result = $table->select('*, user.*, player.*', '', '', TGC_Bot::$JOINS);
		while ($bot = $table->fetch($result, GDO::ARRAY_O))
		{
			$bot instanceof TGC_Bot;
			$bot->setUser(new GWF_User($bot->getGDOData()));
			$bot->afterLoad();
			$this->addBot($bot);
		}
	}
	
	#############
	### Cache ###
	#############
	private function addBot(TGC_Bot $bot)
	{
		$this->typedBots[$bot->getType()] = $bot;
		TGC_Global::addPlayer($bot);
	}
	
	############
	### Tick ###
	############
	public function tick($tick)
	{
		foreach (TGC_Global::$BOTS as $bot)
		{
			$bot instanceof TGC_Bot;
			$bot->tick($tick);
		}
		$this->spawnBots($tick);
	}
	
	
	#############
	### Spawn ###
	#############
	private function spawnBots($tick)
	{
		$chances = array();
		$maxTotal = $this->tgc()->cfgMaxBots();
		$haveTotal = count($this->bots);
		if ($haveTotal < $maxTotal)
		{
			foreach ($this->scripts as $type)
			{
				$have = count($this->typedBots[$type]);
				$max = call_user_func(array($this->tgc(), sprintf('cfgMax%sBots', $type)));
				if ($have < $max)
				{
					$chances[$type] = $max - $have;
				}
			}
			# Any left to spawn?
			if (count($chances) > 0)
			{
				$type = GWF_Random::arrayItem(array_keys($chances));
				$bot = $this->spawnBot($type);
				$bot->afterLoad();
				$this->addBot($bot);
				$this->lastSpawn = $tick;
			}
		}
	}

	private function spawnBot($type)
	{
		$this->spawncounter++;
		$user = new GWF_User(array(
			'user_id' => '0',
			'user_options' => GWF_User::BOT,
			'user_name' => '#'.microtime(true),
			'user_guest_id' => '1',
			'user_guest_name' => '#B#',
			'user_password' => '',
			'user_regdate' => '',
			'user_regip' => GWF_IP6::getIP(GWF_IP_EXACT, '::1'),
			'user_email' => 'BOT'.$this->spawncounter.'@tgc.gizmore.org',
			'user_gender' => GWF_User::NO_GENDER,
			'user_lastlogin' => '0',
			'user_lastactivity' => time(),
			'user_birthdate' => '',
			'user_countryid' => '0',
			'user_langid' => '0',
			'user_langid2' => '0',
			'user_level' => '0',
			'user_title' => '',
			'user_settings' => '',
			'user_data' => '',
			'user_credits' => '0.00',
			'user_saved_at' => GWF_Time::getDate(),
		));
		if (!$user->insert())
		{
			return false;
		}
		$user->saveVars(array(
			'user_name' => '#B#'.$user->getID(),
			'user_guest_name' => '#'.$type.'#'.$this->spawncounter,
		));
		$player = TGC_Player::createPlayer($user);
		$bot = new TGC_Bot(array('b_uid' => $user->getID(), 'b_type' => $type));
		if (!$bot->insert())
		{
			return false;
		}
		
		foreach (TGC_Player::$USER_FIELDS as $field)
		{
			$player->setVar($field, $user->getVar($field));
		}
		
		$bot->setGDOData(array_merge($bot->getGDOData(), $player->getGDOData()));
		$bot->setUser($user);
		
		return $bot;
	}

}