'use strict';
var TGC = angular.module('gwf4');
TGC.service('TGCCommandSrvc', function($rootScope, $injector, ErrorSrvc, WebsocketSrvc, CommandSrvc) {
	
	//////////////////////
	// Injector getters //
	//////////////////////
	CommandSrvc.getMapUtil = function() { CommandSrvc.MAPUTIL = CommandSrvc.MAPUTIL || $injector.get('MapUtil'); return CommandSrvc.MAPUTIL; };
	CommandSrvc.getChatSrvc = function() { CommandSrvc.CHATSERVICE = CommandSrvc.CHATSERVICE || $injector.get('ChatSrvc'); return CommandSrvc.CHATSERVICE; };
	CommandSrvc.getEffectSrvc = function() { CommandSrvc.EFFECTSERVICE = CommandSrvc.EFFECTSERVICE || $injector.get('EffectSrvc'); return CommandSrvc.EFFECTSERVICE; };
	CommandSrvc.getPlayerSrvc = function() { CommandSrvc.PLAYERSERVICE = CommandSrvc.PLAYERSERVICE || $injector.get('PlayerSrvc'); return CommandSrvc.PLAYERSERVICE; };
	CommandSrvc.getLevelupDlg = function() { CommandSrvc.LEVELUPDIALOG = CommandSrvc.LEVELUPDIALOG || $injector.get('LevelupDlg'); return CommandSrvc.LEVELUPDIALOG; };
	
	/////////////////////
	// Client commands //
	/////////////////////
	CommandSrvc.tgcHelo = function(nickname, position) {
		console.log('CommandSrvc.tgcHelo()', nickname, position);
		var payload = {
			user_guest_name: nickname,
			lat: position.lat,
			lng: position.lng,
			domain: GWF_DOMAIN,
			version: TGC_CONFIG.version,
			user_agent: navigator.userAgent,
		};
		return WebsocketSrvc.sendJSONCommand('tgcHelo', payload, false);
	};

	CommandSrvc.tgcPlayer = function(player) {
		console.log('CommandSrvc.tgcPlayer()', player);
		return WebsocketSrvc.sendCommand('tgcPlayer', player.name(), false);
	};
	
	CommandSrvc.tgcPos = function(position) {
		var pos = position.coords;
		return WebsocketSrvc.sendJSONCommand('tgcPos', { lat: pos.latitude, lng: pos.longitude });
	};
	
	CommandSrvc.tgcChat = function(messageText) {
		console.log('CommandSrvc.tgcChat()', messageText);
		return WebsocketSrvc.sendCommand('tgcChat', messageText, false);
	};
	
	CommandSrvc.tgcFight = function(player) {
		console.log('CommandSrvc.fight()', player);
		return WebsocketSrvc.sendCommand('tgcFight', player.name(), false);
	};

	CommandSrvc.tgcAttack = function(player) {
		console.log('CommandSrvc.tgcAttack()', player);
		return WebsocketSrvc.sendCommand('tgcAttack', player.name(), false);
	};
	
	CommandSrvc.tgcBrew = function(player, runes) {
		console.log('CommandSrvc.tgcBrew()', player, runes);
		return WebsocketSrvc.sendJSONCommand('tgcBrew', { target: player.name(), runes: runes });
	};
	
	CommandSrvc.tgcCast = function(player, runes) {
		console.log('CommandSrvc.tgcCast()', player, runes);
		return WebsocketSrvc.sendJSONCommand('tgcCast', { target: player.name(), runes: runes });
	};
	
	/////////////////////
	// Server commands //
	/////////////////////
//	CommandSrvc.PONG = function($scope, payload) {
//		console.log('CommandSrvc.PONG()', payload);
//		$scope.data.version = payload;
//	};
	
	CommandSrvc.TGC_BOTKILL = function(payload) {
		console.log('CommandSrvc.TGC_BOTKILL()', payload);
		var data = JSON.parse(payload);
		var MapUtil = CommandSrvc.getMapUtil();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();
		var killer = PlayerSrvc.getPlayer(data.killer);
		var victim = PlayerSrvc.getPlayer(data.victim);
		PlayerSrvc.removePlayer(victim);
		MapUtil.removePlayer(victim);
	};
	
	CommandSrvc.TGC_SLAP = function(payload) {
		console.log('CommandSrvc.TGC_SLAP()', payload);
		var data = JSON.parse(payload);
		CommandSrvc.getEffectSrvc().onGettingAttacked(data);
		CommandSrvc.getPlayerSrvc().OWN.giveHP(-data.damage);
	};
	
	CommandSrvc.TGC_POS = function(payload) {
		console.log('CommandSrvc.TGC_POS()', payload);
		var data = JSON.parse(payload);
		var name = data.player.user_name;
		var player = null;
		
		var MapUtil = CommandSrvc.getMapUtil();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();

		if (PlayerSrvc.hasPlayer(name)) {
			player = PlayerSrvc.getPlayer(name);
		}
		else {
			player = new TGC_Player(data.player, null, null);
			PlayerSrvc.addPlayer(player);
			MapUtil.addPlayer(player);
		}
		player.moveTo(data.player.lat, data.player.lng)
		MapUtil.movePlayer(player);
		PlayerSrvc.updateCacheForPlayer(player, data);
		return player;
	};
	
	CommandSrvc.TGC_LVLUP = function(payload) {
		console.log('CommandSrvc.TGC_LVLUP()', payload);
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();
		var LevelupDlg = CommandSrvc.getLevelupDlg();
		var data = JSON.parse(payload);
		return LevelupDlg.open(PlayerSrvc.OWN, data);
	};
	
	CommandSrvc.TGC_CHAT = function(payload) {
		console.log('CommandSrvc.CHAT()', payload);
		var MapUtil = CommandSrvc.getMapUtil();
		var ChatSrvc = CommandSrvc.getChatSrvc();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();
		var name = payload.substrUntil(':');
		var text = payload.substrFrom(':');
		var player = PlayerSrvc.getPlayer(name);
		if (player) {
			MapUtil.playerChat(player, text);
			ChatSrvc.playerChat(player, text);
		}
		else {
			console.error('Player not found: '+name);
		}
	};
	
	CommandSrvc.TGC_QUIT = function(payload) {
		console.log('CommandSrvc.QUIT()', payload);
		var MapUtil = CommandSrvc.getMapUtil();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();
		var name = payload;
		var player = PlayerSrvc.getPlayer(name);
		if (player) {
			MapUtil.removeMarkerForPlayer(player);
			PlayerSrvc.removePlayer(player);
		}
		else {
			console.error('Player not found: '+name);
		}
	};
	
	CommandSrvc.TGC_CAST = function(payload) {
		var MapUtil = CommandSrvc.getMapUtil();
		var ChatSrvc = CommandSrvc.getChatSrvc();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();
		var data = JSON.parse(payload);
		console.log(data);
		var OWN = PlayerSrvc.OWN;
//		if (data.failed) {
//		}
		if (data.code) {
			eval(data.code);
		}
		if (data.message) {
			ErrorSrvc.showMessage(data.message, 'Casting');
		}
	};
	
	return CommandSrvc;
});
