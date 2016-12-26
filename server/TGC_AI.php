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
	public function bots() { return TGC_Global::$BOTS; }
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
		$result = $table->select(TGC_Player::userFields(), 'p_type IS NOT NULL', '', TGC_Player::$JOINS);
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
		$haveTotal = count($this->bots());
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
				$this->debugSpawn($bot);
			}
		}
	}

	private function spawnBot($type)
	{
		# User
		$user = GWF_Guest::blankUser(array(
			'user_options' => GWF_User::BOT,
			'user_name' => '#'.microtime(true),
			'user_regdate' => GWF_Time::getDate(),
			'user_saved_at' => GWF_Time::getDate(),
		));
		if (!$user->insert())
		{
			return false;
		}
		if (!$user->saveVars(array(
			'user_name' => '#B#'.$user->getID(),
		)))
		{
			return false;
		}
		
		# Bpt 
		if ($bot = TGC_Bot::createBot($user, $type))
		{
			$bot->setUser($user);
		}
		
		return $bot;
	}
	
	private function debugSpawn(TGC_Bot $bot)
	{
		GWF_Log::logCron(sprintf('Spawned: %s', $bot->debugInfo()));
	}
	

}