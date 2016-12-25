<?php
final class TGC_Bot extends TGC_Player
{
	private $script;
	
	public static $JOINS = array('user', 'player');
	public function getClassName() { return __CLASS__; }
	public function getTableName() { return GWF_TABLE_PREFIX.'tgc_bot'; }
	public function getColumnDefines()
	{
		return array(
			'b_uid' => array(GDO::PRIMARY_KEY|GDO::UINT),
			'b_type' => array(GDO::VARCHAR|GDO::ASCII|GDO::CASE_S, GDO::NOT_NULL, 16),

			'user' => array(GDO::JOIN, GDO::NOT_NULL, array('GWF_User', 'b_uid', 'user_id')),
			'player' => array(GDO::JOIN, GDO::NOT_NULL, array('TGC_Player', 'b_uid', 'p_uid')),
		);
	}
	
	###############
	### Getters ###
	###############
	public function getID() { return $this->getVar('b_id'); }
	public function getType() { return $this->getVar('b_type'); }
	public function handler() { return TGC_AI::instance()->handler(); }
	
	############
	### Stub ###
	############
	public function send($messageText) {}
	
	##############
	### Events ###
	##############
	public function afterLoad()
	{
		$this->script = TGC_AIScript::factory($this);
		return parent::afterLoad();
	}
	
	public function tick($tick)
	{
		return $this->script->tick($tick);
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
		return call_user_func(array($this->handler(), 'cmd_'.$command), $this->getUser(), $payload);
	}
	
	#################
	### Commands ####
	#################
	public function aiMove($lat, $lng)
	{
		$this->setPosition($lat, $lng);
		$this->aiJSONCommand('tgcPos', array('lat' => $lat, 'lng' => $lng));
	}
	
	public function aiFight(TGC_Player $player)
	{
		$this->aiCommand('tgcFight', $player->getName());
	}
	
	###################
	### Move Helper ###
	###################
	public function moveNear(TGC_Player $player)
	{
		$lat = GWF_Random::Rand(0, 1000) / 1000 + $player->lat();
		$lng = GWF_Random::Rand(0, 1000) / 1000 + $player->lng();
		$this->aiMove($lat, $lng);
	}

}
