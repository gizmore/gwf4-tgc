'use strict';
angular.module('gwf4')
.service('EffectSrvc', function(ErrorSrvc, PlayerSrvc, MapUtil, ShapeUtil) {
	
	var EffectSrvc = this;
	
	//////////////
	// Slapping //
	//////////////
	EffectSrvc.onAttack = function(data) {
		console.log('EffectSrvc.onAttack()', data);
		EffectSrvc.onGettingAttacked(data);
	};

	EffectSrvc.onGettingAttacked = function(data) {
		console.log('EffectSrvc.onGettingAttacked()', data);
		var attacker = PlayerSrvc.getPlayer(data.attacker);
		var defender = PlayerSrvc.getPlayer(data.defender);
		EffectSrvc.attackBox(attacker, defender, EffectSrvc.slapHTML(data));
	};
	
	EffectSrvc.slapHTML = function(data) {
		return sprintf('<b>%s</b><br/>%s', EffectSrvc.slapTitle(data), EffectSrvc.slapMessage(data));
	};

	EffectSrvc.slapTitle = function(data) {
		switch (data.type) {
		case 'fighter': return "Fight";
		case 'ninja': return "Attack";
		case 'priest': return "Potion";
		case 'wizard': return "Spell";
		}
	};
	EffectSrvc.slapMessage = function(data) {
		var attacker = PlayerSrvc.getPlayer(data.attacker);
		var defender = PlayerSrvc.getPlayer(data.defender);
		attacker = attacker ? attacker.displayName() : data.attacker;
		defender = defender ? defender.displayName() : data.defender;
		var damage = data.critical ? sprintf('<critical>%s damage</critical>', data.damage) : sprintf('%s damage', data.damage);
		damage = data.killed ? sprintf('<b>Killed</b> with %s!', damage) : sprintf('This caused %s.', damage);
		return sprintf('%s %s %s %s with %s %s.<br/>%s', attacker, data.adverb, data.verb, defender, data.adjective, data.noun, damage);
	}
	
	///////////
	// Magic //
	///////////
	EffectSrvc.onMagic = function(data) {
		console.log('EffectSrvc.onMagic()', data);
		var player = PlayerSrvc.getPlayer(data.player);
		var target = PlayerSrvc.getPlayer(data.target);
		var position = EffectSrvc.magicPosition(data);
		EffectSrvc.attackBoxPos(position, EffectSrvc.magicHTMLContent(data));
	};
	
	EffectSrvc.magicPosition = function(data) {
		if (data.lat) {
			return MapUtil.coordsToLatLng(data.lat, data.lng);
		}
		var target = PlayerSrvc.getPlayer(data.target);
		if (target && target.hasPosition())
		{
			return target.latLng();
		}
		return PlayerSrvc.OWN.latLng();
	};

	EffectSrvc.magicHTMLContent = function(data) {
		return data.message;
	};

	////////////////
	// Attack box //
	////////////////
	EffectSrvc.attackBox = function(attacker, defender, content) {
		var points = [];
		if (attacker) points.push(attacker.latLng());
		if (defender) points.push(defender.latLng());
		return EffectSrvc.attackBoxPos(MapUtil.middle(points), content);
	};
	
	EffectSrvc.attackBoxPos = function(latLng, content) {
		var attackBox = new google.maps.InfoWindow({
			content: content,
			position: latLng,
		});
		attackBox.open(MapUtil.map());
		setTimeout(EffectSrvc.closeAttackBox.bind(EffectSrvc, attackBox), 10000);
	};

	EffectSrvc.closeAttackBox = function(attackBox) {
		attackBox.setMap(null);
		attackBox.close();
	};
	
	return EffectSrvc;
});
