'use strict';
var TGC = angular.module('gwf4');
TGC.service('TGCCommandSrvc', function($rootScope, $injector, ErrorSrvc, WebsocketSrvc, CommandSrvc) {
	
	//////////////////////
	// Injector getters //
	//////////////////////
	CommandSrvc.getMapUtil = function() { CommandSrvc.MAPUTIL = CommandSrvc.MAPUTIL || $injector.get('MapUtil'); return CommandSrvc.MAPUTIL; };
	CommandSrvc.getChatSrvc = function() { CommandSrvc.CHATSERVICE = CommandSrvc.CHATSERVICE || $injector.get('ChatSrvc'); return CommandSrvc.CHATSERVICE; };
	CommandSrvc.getPlayerSrvc = function() { CommandSrvc.PLAYERSERVICE = CommandSrvc.PLAYERSERVICE || $injector.get('PlayerSrvc'); return CommandSrvc.PLAYERSERVICE; };
	
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
	CommandSrvc.PONG = function($scope, payload) {
		console.log('CommandSrvc.PONG()', payload);
		$scope.data.version = payload;
	};
	
	CommandSrvc.POS = function($scope, payload) {
		console.log('CommandSrvc.POS()', payload);
		var data = JSON.parse(payload);
		var name = data.player.name;
		var player = null;
		
		var MapUtil = CommandSrvc.getMapUtil();
		var PlayerSrvc = CommandSrvc.getPlayerSrvc();

		if (PlayerSrvc.hasPlayer(name)) {
			player = PlayerSrvc.getPlayer(name);
		}
		else {
			player = new window.TGC.Player(data.player, null, null);
			PlayerSrvc.addPlayer(player);
			MapUtil.addPlayer(player);
		}
		player.moveTo(data.pos.lat, data.pos.lng)
		MapUtil.movePlayer(player);
		PlayerSrvc.updateCacheForPlayer(player, data);
		return player;
	};
	
	CommandSrvc.CHAT = function($scope, payload) {
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
	
	CommandSrvc.QUIT = function($scope, payload) {
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
	
	CommandSrvc.CAST = function($scope, payload) {
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
