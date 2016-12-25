'use strict';
angular.module('gwf4')
.controller('TGCCtrl', function($scope, $q, ConstSrvc, PlayerSrvc, AuthSrvc, ErrorSrvc, PositionSrvc, WebsocketSrvc, TGCCommandSrvc) {
	
	$scope.data = {
		version: '',
		inited: false,
		nickname: '',
	};

	$scope.init = function() {
		console.log('TGCCtrl.init()');
	};
	
	////////////
	// Events //
	////////////
	$scope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
		console.log('TGCCtrl.$on-stateChangeSuccess', toState);
		if (!$scope.inited) {
			$scope.inited = true;
			$scope.init();
		}
	});
	
	$scope.$on('tgc-position-changed', function(event, position) {
		console.log('TGCCtrl.$on-tgc-position-changed', position);
		TGCCommandSrvc.tgcPos(position);
	});

	////////////
	// Loader //
	////////////
	$scope.navigateToMap = function() {
		console.log('TGCCtrl.navigateToMap()');
		PositionSrvc.withPosition().then($scope.withPosition, $scope.withoutPosition);
	};

	$scope.withPosition = function() {
		console.log('TGCCtrl.withPosition()');
		AuthSrvc.withNickname().then($scope.withNickname);
	};
	
	$scope.withoutPosition = function() {
		console.log('TGCCtrl.withoutPosition()');
	};
	
	$scope.withNickname = function(nickname) {
		console.log('TGCCtrl.withNickname()', nickname);
		$scope.data.nickname = nickname;
		WebsocketSrvc.withConnection().then($scope.withConnection, $scope.withoutConnection);
	};
	
	$scope.withConnection = function() {
		console.log('TGCCtrl.withConnection()');
		TGCCommandSrvc.tgcHelo($scope.data.nickname, PositionSrvc.CURRENT).then(function(data){
			$scope.loadMap(JSON.parse(data));
		});
	};
	
	$scope.withoutConnection = function() {
		console.log('TGCCtrl.withoutConnection()');
	};
	
	$scope.loadMap = function(ehloData) {
		console.log('TGCCtrl.loadMap()', ehloData);
		$scope.data.timestamp = ehloData.timestamp;
		ErrorSrvc.showMessage(ehloData.welcome_message, 'TGC '+ehloData.server_version);
		PlayerSrvc.OWN = PlayerSrvc.updatePlayerCache(ehloData.player);
		$scope.refreshSidebar().then(function() {
			$scope.requestPage(GWF_WEB_ROOT+'tgc-game?ajax=1');
		});
	};

});
