'use strict';
angular.module('gwf4')
.service('EffectSrvc', function(PlayerSrvc, MapUtil, ShapeUtil) {
	
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
		var points = [];
		if (attacker) {
			points.push(attacker.latLng());
		}
		if (defender) {
			points.push(defender.latLng());
		}
		var map = MapUtil.map();
		var attackBox = new google.maps.InfoWindow({
			content: EffectSrvc.slapHTML(data),
			position: MapUtil.middle(points),
		});
		attackBox.open(map);
		setTimeout(EffectSrvc.afterGettingAttacked.bind(EffectSrvc, attackBox, data), 10000);
	};
	
	EffectSrvc.afterGettingAttacked = function(attackBox, data) {
		attackBox.setMap(null);
		attackBox.close();
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
		var damage = data.critical ? sprintf('<critical>%s damage</critical>', data.damage) : data.damage;
		damage = data.killed ? sprintf('<b>Killed</b> with %s!', damage) : sprintf('This caused %s.', damage);
		return sprintf('%s %s %s %s with %s %s.<br/>%s', data.attacker, data.adverb, data.verb, data.defender, data.adjective, data.noun, damage);
	}

	
	return EffectSrvc;
});
