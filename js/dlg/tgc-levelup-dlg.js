'use strict';
angular.module('gwf4')
.service('LevelupDlg', function($q, $mdDialog, ErrorSrvc) {

	var LevelupDlg = this;
	
	LevelupDlg.open = function(player, data) {
		console.log('LevelupDlg.open()', player, data);
		return $q(function(resolve, reject){
			LevelupDlg.show(player, data, resolve, reject);
		});
	};

	LevelupDlg.show = function(player, data, resolve, reject) {
		function DialogController($scope, $mdDialog, player, data) {
			$scope.player = player;
			$scope.data = data;
			$scope.closeDialog = function() {
				$mdDialog.hide();
				resolve();
			};
		}
		var parentEl = angular.element(document.body);
		$mdDialog.show({
//			parent: document.getElementById('TGCMAP'),
//			targetEvent: $event,
			templateUrl: GWF_WEB_ROOT+'module/Tamagochi/js/tpl/levelup_dlg.html',
			locals: {
				player: player,
				data: data,
			},
			controller: DialogController
		});
	};
	
	return LevelupDlg;
});
