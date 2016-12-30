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
		TGC_Spell::init();
		$this->ai = new TGC_AI();
		$this->ai->init($this);
		$this->timer();
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
		TGC_Global::removePlayer($player);
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
	
	public function cmd_tgcRace(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$data = json_decode($payload);
			if ($player->getRace() !== TGC_Race::NONE)
			{
				return $player->sendError('ERR_RACE_ALREADY_SET');
			}
			if (!TGC_Race::validHumanRace($data->race))
			{
				return $player->sendError('ERR_UNKNOWN_RACE');
			}
			$player->setupRace($data->race);
			$payload = json_encode($player->ownPlayerDTO($user));
			GWS_Global::sendCommand($user, 'TGC_OWNP', self::payload($payload, $mid));
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcGender(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$data = json_decode($payload);
			if ($player->getGender() !== 'no_gender')
			{
				return $player->sendError('ERR_GENDER_ALREADY_SET');
			}
			if (!TGC_Race::validGender($data->gender))
			{
				return $player->sendError('ERR_UNKNOWN_GENDER');
			}
			$player->setupGender($data->gender);
			$payload = json_encode($player->ownPlayerDTO($user));
			GWS_Global::sendCommand($user, 'TGC_OWNP', self::payload($payload, $mid));
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
	public function cmd_tgcPause(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$payload = json_encode($player->pauseDTO());
			$player->sendCommand('TGC_PAUSE', $payload);
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
			$payload = $p->getUserID() === $user->getID() ? $p->ownPlayerDTO() : $p->otherPlayerDTO();
			GWS_Global::sendCommand($user, 'TGC_PLAYER', self::payload(json_encode($payload), $mid));
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
			$payload = json_encode($player->userPositionDTO());
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
			if (!($target = self::playerNamed($data->target)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			if (!($potion = TGC_Potion::factory($player, $target, 'BREW', $data->runes, $mid)))
			{
				return $player->sendError('ERR_UNKNOWN_POTION');
			}
			$potion->brew();
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
			if (!($target = self::playerNamed($data->target)))
			{
				return $player->sendError('ERR_UNKNOWN_PLAYER');
			}
			if (!($spell = TGC_Spell::factory($player, $target, 'CAST', $data->runes, $mid)))
			{
				return $player->sendError('ERR_UNKNOWN_SPELL');
			}
			$spell->cast();
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}

	public function cmd_tgcCastLL(GWF_User $user, $payload, $mid)
	{
		try {
			$player = self::player($user);
			$data = json_decode($payload);
			if (!TGC_Logic::isPlayerNear($player, $data->lat, $data->lng))
			{
				return $player->sendError('ERR_NOT_NEAR');
			}
			$target = array($data->lat, $data->lng);
			if (!($spell = TGC_Spell::factory($player, $target, 'CAST', $data->runes, $mid)))
			{
				return $player->sendError('ERR_UNKNOWN_SPELL');
			}
			$spell->cast();
		}
		catch (Exception $e) {
			GWS_Global::sendError($user, $e->toString());
		}
	}
	
}
