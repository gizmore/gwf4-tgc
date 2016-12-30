'use strict';
angular.module('gwf4')
.controller('TGCActionbarCtrl', function($scope, PlayerDlg, SpellDlg) {
	
	$scope.data = {
	};

	$scope.doPause = function() {
		TGCCommandSrvc.tgcPause();
	};
	
	$scope.doSelf = function() {
		PlayerDlg.open(PlayerSrvc.OWN);
	};
	
	$scope.doBrew = function() {
		SpellDlg.open(PlayerSrvc.OWN, 'brew');
	};

	$scope.doCast = function() {
		SpellDlg.open(PlayerSrvc.OWN, 'cast');
	};
	
});
