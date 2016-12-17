<?php
final class TGC_Commands extends GWS_Commands
{
// 	public static function cmd_stats(TGC_Player $player, $payload, $mid)
// 	{
// 		$stats = array(
// 			'players' => count(TGC_Global::$PLAYERS),
// 			'memory' => memory_get_usage(),
// 			'peak' => memory_get_peak_usage(true),
// 			'cpu' => 1.00,
// 		);
// 		$player->sendJSONCommand('STATS', $stats);
// 	}
	
	public function cmd_chat(TGC_Player $player, $payload, $mid)
	{
		$payload = $player->getName().':'.$payload;
		$player->forNearMe(function($p, $payload){
			$p->sendCommand('CHAT', $payload);
		}, $payload);
		$player->sendCommand('CHAT', $payload);
	}
	
	public function cmd_pos(TGC_Player $player, $payload, $mid)
	{
		$coords = json_decode($payload);
		
		$player->moveTo($coords->lat, $coords->lng);
		
		$payload = json_encode(array(
			'player' => array_merge(array('name' => $player->getName(), 'hash' => $player->getStatsHash())),
			'pos' => array(
				'lat' => $coords->lat,
				'lng' => $coords->lng,
			),
		));

		$player->sendCommand('POS', $payload);
		$player->forNearMe(function($p, $payload) {
			$p->sendCommand('POS', $payload);
		}, $payload);
		
// 		player->initialPositionUpdate();
	}
	
	public function cmd_player(TGC_Player $player, $payload, $mid)
	{
		if (!($p = TGC_ServerUtil::getPlayerForName($payload)))
		{
			return $player->sendError('ERR_UNKNOWN_PLAYER');
		}
		$payload = json_encode(array(
			'player' => array_merge(array('name' => $p->getName(), 'hash' => $p->getStatsHash()), $p->playerDTO()),
		));
		$player->sendCommand('PLAYER', self::payload($payload, $mid));
	}
	
	public function cmd_fight(TGC_Player $player, $payload, $mid)
	{
		if (!($p = TGC_ServerUtil::getPlayerForName($payload)))
		{
			return $player->sendError('ERR_UNKNOWN_PLAYER');
		}
		$attack = new TGC_Attack($player, $p, $mid);
		$attack->dice('fighter');
	}
	
	public function cmd_attack(TGC_Player $player, $payload, $mid)
	{
		if (!($p = TGC_ServerUtil::getPlayerForName($payload)))
		{
			return $player->sendError('ERR_UNKNOWN_PLAYER');
		}
	
		$attack = new TGC_Attack($player, $p, $mid);
		$attack->dice('ninja');
	}
	
	public function cmd_brew(TGC_Player $player, $payload, $mid)
	{
		$data = json_decode($payload);
		if (!($p = TGC_ServerUtil::getPlayerForName($data->target)))
		{
			return $player->sendError('ERR_UNKNOWN_PLAYER');
		}
		if ($potion = TGC_Potion::factory($player, $p, 'BREW', $data->runes, $mid))
		{
			$potion->brew();
		}
	}
	
	public function cmd_cast(TGC_Player $player, $payload, $mid)
	{
		$data = json_decode($payload);
		if (!($p = TGC_ServerUtil::getPlayerForName($data->target)))
		{
			return $player->sendError('ERR_UNKNOWN_PLAYER');
		}
		if ($spell = TGC_Spell::factory($player, $p, 'CAST', $data->runes, $mid))
		{
			$spell->cast();
		}
	}
}
