'use strict';
var TGC = angular.module('gwf4');
TGC.controller('TGCMapCtrl', function($scope, PlayerSrvc, PositionSrvc, MapUtil, TGCMapUtil) {

	$scope.data = {
		map: MapUtil.tgcMap(),
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
		MapUtil.TGC_MAP.setCenter(MapUtil.positionToLatLng(position));
	};
	$scope.$on('tgc-position-changed', $scope.positionChanged);
	$scope.init();
});
