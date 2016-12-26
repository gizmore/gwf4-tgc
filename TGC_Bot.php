<?php
final class TGC_Bot extends TGC_Player
{
	private $script;
	private $command = null;
	
	public function getClassName() { return __CLASS__; }
	
	###############
	### Getters ###
	###############
	public function getID() { return $this->getVar('p_uid'); }
	public function getType() { return $this->getVar('p_type'); }
	public function target() { return $this->script->target(); }
	public function handler() { return TGC_AI::instance()->handler(); }
	public function lastCommand() { return $this->command; }
	
	############
	### Stub ###
	############
	public function send($messageText) { printf("%s << %s\n", $this->displayName(), $messageText); }
	
	##############
	### Events ###
	##############
	public function afterLoad()
	{
		parent::afterLoad();
		$this->script = TGC_AIScript::factory($this);
	}
	
	public function tick($tick)
	{
		$this->command = null;
		$this->script->tick($tick);
		if ($this->command)
		{
			list($command, $payload) = $this->command;
			$this->tickExecute($command, $payload);
		}
	}
	
	private function tickExecute($command, $payload)
	{
		printf('%s >> %s:%s', $this->displayName(), $command, $payload);
		$method = array($this->handler(), 'cmd_'.$command);
		call_user_func($method, $this->getUser(), $payload, GWS_Commands::DEFAULT_MID);
	}
	
	###################
	### Move Helper ###
	###################
	public function aiMoveNear($player, $instant=false)
	{
		if ($player && $player->hasPosition())
		{
			$lat = GWF_Random::Rand(0, 1000) / 1000 + $player->lat();
			$lng = GWF_Random::Rand(0, 1000) / 1000 + $player->lng();
			$this->aiMove($lat, $lng, $instant);
		}
	}
	
	###############
	### Command ###
	###############
	public function aiJSONCommand($command, array $object)
	{
		return $this->aiCommand($command, json_encode($object));
	}
	
	public function aiCommand($command, $payload)
	{
		if (!$this->command)
		{
			$this->command = array($command, $payload);
		}
	}
	
	#################
	### Commands ####
	#################
	public function aiMove($lat, $lng, $instant=false)
	{
		$this->setPosition($lat, $lng);
		$payload = array('lat' => $lat, 'lng' => $lng);
		if ($instant)
		{
			$this->tickExecute('tgcPos', $payload);
		}
		else
		{
			$this->aiJSONCommand('tgcPos', $payload);
		}
	}
	
	public function aiFight($player, $command='tgcFight')
	{
		if ($player)
		{
			$this->aiCommand($command, $player->getName());
		}
	}
	
	public function aiAttack($player)
	{
		$this->aiFight($player, 'tgcAttack');
	}
	
	public function aiCast($player, $spell)
	{
	
	}
	
	public function aiBrew($player, $spell)
	{
	
	}
	

}
