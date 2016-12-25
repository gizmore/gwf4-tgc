'use strict';
var TGC = angular.module('gwf4');
TGC.controller('TGCMapCtrl', function($scope, PlayerSrvc, TGCMapUtil, MapUtil, PositionSrvc) {

	var map = MapUtil.map('TGCMAP');
	
	$scope.data = {
		map: map,
	};
	
	$scope.init = function() {
		console.log('TGCMapCtrl.init()');
		MapUtil.updatePlayer(PlayerSrvc.OWN);
	};
	
	////////////
	// Events //
	////////////
	$scope.positionChanged = function($event, position) {
		console.log('MapCtrl.positionChanged()', position);
		// Recenter yourself
		MapUtil.center
		var map = MapUtil.map('TGCMAP');
		map.setCenter(MapUtil.positionToLatLng(position));
	};
	$scope.$on('tgc-position-changed', $scope.positionChanged);
	$scope.init();
});
