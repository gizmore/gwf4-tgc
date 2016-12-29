'use strict';
angular.module('gwf4')
.service('TGCMapUtil', function(MapUtil, ColorUtil, AreaDlg, PlayerDlg, PlayerSrvc, ShapeUtil) {
	
	MapUtil.MARKERS = {};

	MapUtil.gwfMap = MapUtil.map;
	MapUtil.map = function(id) {
		if (MapUtil.MAP) {
			return MapUtil.MAP;
		}
		var map = MapUtil.gwfMap(id);
		if (!map) {
			return undefined;
		}
		MapUtil.MAP.addListener('click', MapUtil.mapClicked);
		MapUtil.MAP.addListener('center_changed', MapUtil.panBack);
		return MapUtil.MAP;
	};
	
	MapUtil.mapClicked = function(event) {
		console.log('MapUtil.mapClicked()', event);
		AreaDlg.open(event.latLng);
	};
	
	MapUtil.panBack = function() {
		if (MapUtil.PAN_TIMER) {
			clearTimeout(MapUtil.PAN_TIMER);
			MapUtil.PAN_TIMER = null;
		}
		if ( (PlayerSrvc.OWN) && (PlayerSrvc.OWN.hasPosition()) && (!PlayerSrvc.OWN.NO_SCROLL_LOCK) ) {
			MapUtil.PAN_TIMER = window.setTimeout(function() {
				MapUtil.MAP.panTo(PlayerSrvc.OWN.latLng());
			}, MapUtil.PAN_TIMEOUT);
		}
	};
	
	MapUtil.playerChat = function(player, message) {
		console.log('MapUtil.playerChat()', player, message);
		if (player.marker) {
			player.marker.setLabel(player.name()+': '+message);
			setTimeout(function(){
				player.marker.setLabel(player.name());
			}, 5000);
		};
	};

	/////////////
	// Markers //
	/////////////
	MapUtil.updatePlayer = function(player) {
		if (player.hasPosition()) {
			MapUtil.movePlayer(player);
		}
	};

	MapUtil.removePlayer = function(player) {
		return MapUtil.removeMarkerForPlayer(player);
	};
	
	MapUtil.playerForMarker = function(marker) {
		for (var player in MapUtil.MARKERS) {
			if (MapUtil.MARKERS[player] === marker) {
				return player;
			}
		}
		return undefined;
	};
	
	MapUtil.addMarkerForPlayer = function(player) {
		console.log('MapUtil.addMarkerForPlayer()', player, player.lat(), player.lng());
		player.marker = new google.maps.Marker({
			position: player.latLng(),
			map: MapUtil.map(),
			title: player.user.displayName(),
			label: player.user.displayName(),
			size: MapUtil.sizeForPlayer(player),
//			image: MapUtil.imageForPlayer(player),
		});
		player.marker.addListener('click', function(event) {
			PlayerDlg.open(event, player);
		});
		
		MapUtil.MARKERS[player] = player.marker;

		return player.marker;
	};

	MapUtil.removeMarkerForPlayer = function(player) {
		if (player.marker) {
			player.marker.setMap(null);
			player.marker = undefined;
		}
		if (player.shape) {
			player.shape.setMap(null);
			player.shape = undefined;
		}
		MapUtil.MARKERS[player] = undefined;
	}
	
	MapUtil.addPlayer = function(player) {
		return player;
	};
	
	MapUtil.movePlayer = function(player) {
		console.log('MapUtil.movePlayer()', player, player.lat(), player.lng());
		var marker = player.marker ? player.marker : MapUtil.addMarkerForPlayer(player);
		MapUtil.styleMarkerForPlayer(player);
		ShapeUtil.initShape(player, MapUtil.map());
		ShapeUtil.addPlayer(player, MapUtil.map());
	};
	
	MapUtil.styleMarkerForPlayer = function(player) {
		var marker = player.marker;
		marker.setPosition(player.latLng());
		jQuery(marker).animate({
			color: ColorUtil.colorForPlayer(player),
		}, 5000);
	};

	MapUtil.imageForPlayer = function(player) {
	};

	MapUtil.sizeForPlayer = function(player) {
		return 41.13 + player.fighterLevel() + player.ninjaLevel() + player.priestLevel() + player.wizardLevel(); 
	};

});
