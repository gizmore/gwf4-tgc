<?php
require_once 'TGC_Global.php';

final class TGC_Commands extends GWS_Commands
{
	private $ai;
	private $tgc;
	private $acc;
	
	############
	### Init ###
	############
	public function init()
	{
		GWF_Log::logCron('TGC_Commands::init()');
		$this->tgc = Module_Tamagochi::instance();
		$this->acc = GWF_Module::loadModuleDB('Account', true, true);
// 		$this->changeNick = $this->modAccount->getMethod('ChangeGuestNickname');
		TGC_Global::init(31337);
		$this->ai = new TGC_AI();
		$this->ai->init($this);
	}
	
	#############
	### Timer ###
	#############
	public function timer()
	{
		$this->ai->tick(TGC_Global::tick());
	}
	
	##############
	### Events ###
	##############
	public function disconnect(GWF_User $user) {
		parent::disconnect($user);
		$player = self::player($user);
		TGC_Global::removePlayer($user->getName());
		foreach (TGC_Global::$PLAYERS as $remainingPlayer)
		{
// 			if ($player->isNear($remainingPlayer))
			{
				$remainingPlayer->sendCommand('TGC_QUIT', $user->getName());
			}
		}
	}

	# Util
	private static function player(GWF_User $user) { return TGC_Global::getOrCreatePlayer($user); }
	private static function playerNamed($name) { return TGC_Global::getPlayer($name); }

	################
	### Commands ###
	################
	public function cmd_tgcHelo(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$payload = json_decode($payload);
			$navigator = $payload->user_agent;
			$player->setVar('user_guest_name', preg_replace('/[^_a-z0-9]*/i', '', $payload->user_guest_name));
			$player->moveTo($payload->lat, $payload->lng);
			$payload = json_encode(array(
				'player' => $player->ownPlayerDTO($user),
				'welcome_message' => $this->tgc->cfgWelcomeMessage(),
				'server_version' => $this->tgc->getVersion(),
			));
			GWS_Global::sendCommand($user, 'TGC_HELO', self::payload($payload, $mid));
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcChat(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$payload = $player->getName().':'.$payload;
			$player->forNearMe(function($p, $payload){
				$p->sendCommand('TGC_CHAT', $payload);
			}, $payload);
			$player->sendCommand('TGC_CHAT', $payload);
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcPlayer(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			if (!($p = self::playerNamed($payload)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			$payload = json_encode(array(
				'player' => $p->otherPlayerDTO(),
				'tick' => TGC_Global::$TICK,
			));
			GWS_Global::sendCommand($user, 'TGC_HELO', self::payload($payload, $mid));
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcPos(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$payload = json_decode($payload);
			$player->moveTo($payload->lat, $payload->lng);
			$payload = json_encode(array(
				'player' => $player->userPositionDTO(),
			));
			$player->sendCommand('TGC_POS', $payload);
			$player->forNearMe(function($p, $payload) {
				$p->sendCommand('TGC_POS', $payload);
			}, $payload);
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}

	public function cmd_tgcFight(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			if (!($p = self::playerNamed($payload)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			$attack = new TGC_Attack($player, $p, $mid);
			$attack->dice('fighter');
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcAttack(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			if (!($p = self::playerNamed($payload)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
		
			$attack = new TGC_Attack($player, $p, $mid);
			$attack->dice('ninja');
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcBrew(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$data = json_decode($payload);
			if (!($p = self::playerNamed($data->target)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			if ($potion = TGC_Potion::factory($player, $p, 'BREW', $data->runes, $mid))
			{
				$potion->brew();
			}
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcCast(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$data = json_decode($payload);
			if (!($p = self::playerNamed($data->target)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			if ($spell = TGC_Spell::factory($player, $p, 'CAST', $data->runes, $mid))
			{
				$spell->cast();
			}
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
}
