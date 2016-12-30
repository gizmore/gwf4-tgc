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
	public function getScript() { return $this->script; }
	public function lastCommand() { return $this->command; }
	public function killXP(TGC_Player $killer) { return 1 + $this->playerLevel(); }
	
	############
	### Stub ###
	############
	public function send($messageText) { printf("%s << %s\n", $this->displayName(), $messageText); }
	
	############
	### Kill ###
	############
	public function killedBy(TGC_Player $killer)
	{
		if ($this->deletePlayer())
		{
			$payload = json_encode(array(
				'killer' => $killer->getName(),
				'victim' => $this->getName(),
			));
			$this->forNearMe(function(TGC_Player $player, $payload) {
				$player->sendCommand('TGC_BOTKILL', $payload);
			}, $payload);
		}
	}
	
	public function deletePlayer()
	{
		if (parent::deletePlayer())
		{
			return $this->getUser()->delete();
		}
		return false;
	}
	
	##############
	### Events ###
	##############
	public function afterLoad()
	{
		$this->script = TGC_AIScript::factory($this);
		$this->initBotPosition();
		parent::afterLoad();
	}
	
	private function initBotPosition()
	{
		$this->setPosition(52.0+rand(1, 1000)/1000, 10.0+rand(1, 1000)/1000);
	}
	
	public function tick($tick)
	{
		parent::tick($tick);
		$this->botPos();
		$this->command = null;
		$this->script->tick($tick);
		if ($this->command)
		{
			list($command, $payload) = $this->command;
			$this->tickExecute($command, $payload);
		}
	}
	
	private function botPos()
	{
		$this->aiMove($this->lat(), $this->lng(), true);
	}
	
	private function tickExecute($command, $payload)
	{
		printf("%s >> %s:%s\n", $this->displayName(), $command, $payload);
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
			$this->tickExecute('tgcPos', json_encode($payload));
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
